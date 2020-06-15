<?php

namespace macropage\LaravelSchedulerWatcher;

use macropage\LaravelSchedulerWatcher\Models\job_event_outputs;
use macropage\LaravelSchedulerWatcher\Models\job_events;
use macropage\LaravelSchedulerWatcher\Models\jobs;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Collection;

trait LaravelSchedulerWatcher {

    private $measure_times = [];

    public function monitor(Schedule $schedule): void {
        $events = new Collection($schedule->events());
        $events->each(function (Event $event) {

            $switches = [];

            if ($event->description) {
                preg_match_all('/.*\[(.*)]$/m', $event->description, $matches, PREG_SET_ORDER, 0);
                if (count($matches)) {
                    $switches = explode(',', $matches[0][1]);
                }
            }

            if (in_array('log', $switches, true)) {

                if (preg_match('/\'artisan\'/', $event->command)) {
                    $commandSplittet = explode('\'artisan\'', $event->command);
                    $getMutexCall    = trim($commandSplittet[1]) . ' --mutex';

                    \Artisan::call($getMutexCall);
                    $output       = \Artisan::output();
                    $customMutexd = trim($output);
                } else {
                    $customMutexd = md5($event->command);
                }

                $Description = ($event->description) ?: $event->command;

                /**
                 * Skip job if last run ended with exitcode>0
                 * if you want to ignore last exitcode: use [force] in job-description
                 */
                $event->skip(static function () use ($switches, $customMutexd) {
                    $last_job_event = job_events::whereHas('job', static function ($query) use ($customMutexd) {
                        $query->whereJobMd5($customMutexd);
                    })->orderByDesc('jobe_id')->first('jobe_exitcode');

                    return $last_job_event && $last_job_event->jobe_exitcode && !in_array('force', $switches, true);
                });


                $outputLogFile                                  = sys_get_temp_dir() . '/' . $customMutexd . '.scheduler.output.log';
                $this->measure_times[$customMutexd]['start']    = 0;
                $this->measure_times[$customMutexd]['duration'] = 0;

                $event->before(function () use ($customMutexd) {
                    $this->measure_times[$customMutexd]['start']      = microtime(true);
                    $this->measure_times[$customMutexd]['start_date'] = Carbon::now();
                });

                $event->sendOutputTo($outputLogFile)->after(function () use ($customMutexd, $outputLogFile, $event, $Description, $switches) {

                    $this->measure_times[$customMutexd]['duration'] = microtime(true) - $this->measure_times[$customMutexd]['start'];
                    $this->measure_times[$customMutexd]['end_date'] = Carbon::now();

                    if (file_exists($outputLogFile) && $logData = file_get_contents($outputLogFile)) {
                        \DB::connection(config('scheduler-watcher.mysql_connection'))->transaction(function () use ($logData, $customMutexd, $event, $Description, $switches) {
                            /** @var jobs $jobFound */
                            $jobFound = jobs::where('job_md5', '=', $customMutexd)->first('job_id');
                            if (!$jobFound) {
                                $job              = new jobs();
                                $job->job_md5     = $customMutexd;
                                $job->job_name    = $Description;
                                $job->job_command = $event->command;
                                $job->save();
                                $job_id = $job->job_id;
                            } else {
                                $job_id = $jobFound->job_id;
                            }
                            $jobEvent = new job_events([
                                                           'jobe_job_id'   => $job_id,
                                                           'jobe_start'    => $this->measure_times[$customMutexd]['start_date'],
                                                           'jobe_end'      => $this->measure_times[$customMutexd]['end_date'],
                                                           'jobe_exitcode' => ($event->exitCode) ?: 0,
                                                           'jobe_duration' => $this->measure_times[$customMutexd]['duration'],
                                                       ]);
                            $jobEvent->save();
                            if (!in_array('nooutput', $switches, true)) {
                                $jobEventOutput = new job_event_outputs([
                                                                            'jobo_jobe_id' => $jobEvent->jobe_id,
                                                                            'jobo_output'  => mb_substr($logData,0,21844)
                                                                        ]);
                                $jobEventOutput->save();
                            }
                        }, 5);
                    }
                });
            }
        });
    }
}
