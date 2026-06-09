<?php

namespace macropage\LaravelSchedulerWatcher;

use Carbon\Carbon;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use macropage\LaravelSchedulerWatcher\Models\job_event_outputs;
use macropage\LaravelSchedulerWatcher\Models\job_events;
use macropage\LaravelSchedulerWatcher\Models\jobs;

class LaravelSchedulerWatcher
{

    public static function monitor(): void
    {
        $schedule = app(Schedule::class);
        $events   = new Collection($schedule->events());

        $events->each(function (Event $event) {
            $switches = [];

            if ($event->description) {
                preg_match_all('/.*\[(.*)]$/m', $event->description, $matches, PREG_SET_ORDER);
                if (count($matches)) {
                    $switches = array_map('trim', explode(',', $matches[0][1]));
                }
            }

            if (!in_array('log', $switches, true)) {
                return;
            }

            if ($event->runInBackground && !$event->withoutOverlapping) {
                logger()->warning('Laravel Scheduler Watcher: [log] background events require withoutOverlapping(); event skipped.', [
                    'command'     => $event->command,
                    'description' => $event->description,
                ]);

                return;
            }

            if (str_contains($event->command, '\'artisan\'')) {
                $commandSplittet = explode('\'artisan\'', $event->command);
                $customMutexd    = md5(trim($commandSplittet[1]));
            } else {
                $customMutexd = md5($event->command);
            }

            $description   = ($event->description) ?: $event->command;
            $connection    = config('laravel-scheduler-watcher.mysql_connection');
            $maxRuntime    = self::getMaxRuntimeMinutes($switches);
            $outputLogFile = sys_get_temp_dir() . '/' . $customMutexd . '.scheduler.output.log';
            $idFile        = sys_get_temp_dir() . '/' . $customMutexd . '.scheduler.eventid';

            /**
             * Skip job if last finished run ended with exitcode>0.
             * If you want to ignore last exitcode: use [force] in job-description.
             */
            $event->skip(static function () use ($switches, $customMutexd) {
                $last_job_event = job_events::whereHas('job', static function ($query) use ($customMutexd) {
                    $query->whereJobMd5($customMutexd);
                })->whereNotNull('jobe_exitcode')->orderByDesc('jobe_id')->first('jobe_exitcode');

                return $last_job_event && $last_job_event->jobe_exitcode && !in_array('force', $switches, true);
            });

            $event->before(function () use ($customMutexd, $description, $event, $connection, $idFile, $maxRuntime) {
                DB::connection($connection)->transaction(function () use ($customMutexd, $description, $event, $idFile, $maxRuntime) {
                    $job = self::firstOrCreateJob($customMutexd, $description, $event, $maxRuntime);

                    $jobEvent = new job_events([
                        'jobe_job_id'   => $job->job_id,
                        'jobe_start'    => Carbon::now(),
                        'jobe_end'      => null,
                        'jobe_exitcode' => null,
                        'jobe_duration' => null,
                    ]);
                    $jobEvent->save();

                    file_put_contents($idFile, $jobEvent->jobe_id . '|' . microtime(true));
                }, 5);
            });

            $event->sendOutputTo($outputLogFile)->after(function () use ($customMutexd, $description, $event, $connection, $idFile, $outputLogFile, $switches, $maxRuntime) {
                DB::connection($connection)->transaction(function () use ($customMutexd, $description, $event, $idFile, $outputLogFile, $switches, $maxRuntime) {
                    $idFileContents = is_file($idFile) ? file_get_contents($idFile) : false;
                    [$jobeId, $startMicro] = array_pad(explode('|', $idFileContents ?: ''), 2, null);

                    $jobEvent = is_numeric($jobeId)
                        ? job_events::where('jobe_id', (int) $jobeId)
                                    ->whereNull('jobe_end')
                                    ->whereHas('job', static function ($query) use ($customMutexd) {
                                        $query->whereJobMd5($customMutexd);
                                    })->first()
                        : null;

                    if (!$jobEvent) {
                        $jobEvent = job_events::whereHas('job', static function ($query) use ($customMutexd) {
                            $query->whereJobMd5($customMutexd);
                        })->whereNull('jobe_end')->orderByDesc('jobe_id')->first();
                    }

                    if (!$jobEvent) {
                        $job = self::firstOrCreateJob($customMutexd, $description, $event, $maxRuntime);

                        $jobEvent = new job_events([
                            'jobe_job_id' => $job->job_id,
                            'jobe_start'  => Carbon::now(),
                        ]);
                    }

                    $jobEvent->jobe_end      = Carbon::now();
                    $jobEvent->jobe_exitcode = (int) (($event->exitCode) ?: 0);
                    $jobEvent->jobe_duration = is_numeric($startMicro) ? max(0, microtime(true) - (float) $startMicro) : 0;
                    $jobEvent->save();

                    if (!in_array('nooutput', $switches, true)
                        && is_file($outputLogFile)
                        && ($logData = file_get_contents($outputLogFile)) !== false
                        && $logData !== ''
                    ) {
                        $jobEventOutput = new job_event_outputs([
                            'jobo_jobe_id' => $jobEvent->jobe_id,
                            'jobo_output'  => mb_substr($logData, 0, 21844)
                        ]);
                        $jobEventOutput->save();
                    }

                    if (is_file($idFile)) {
                        unlink($idFile);
                    }
                }, 5);
            });
        });
    }

    private static function getMaxRuntimeMinutes(array $switches): ?int
    {
        foreach ($switches as $switch) {
            if (preg_match('/^maxruntime=(\d+)$/', $switch, $matches) !== 1) {
                continue;
            }

            $minutes = (int) $matches[1];

            return $minutes > 0 ? $minutes : null;
        }

        return null;
    }

    private static function firstOrCreateJob(string $customMutexd, string $description, Event $event, ?int $maxRuntime): jobs
    {
        $job = jobs::firstOrCreate(
            ['job_md5' => $customMutexd],
            [
                'job_name' => $description,
                'job_command' => $event->command,
                'job_max_runtime_minutes' => $maxRuntime,
            ]
        );

        $currentMaxRuntime = $job->job_max_runtime_minutes === null ? null : (int) $job->job_max_runtime_minutes;

        if ($currentMaxRuntime !== $maxRuntime) {
            $job->job_max_runtime_minutes = $maxRuntime;
            $job->save();
        }

        return $job;
    }
}
