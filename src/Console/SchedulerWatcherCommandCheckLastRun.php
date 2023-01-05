<?php

namespace macropage\LaravelSchedulerWatcher\Console;

use Codedungeon\PHPCliColors\Color;
use macropage\LaravelSchedulerWatcher\Models\job_event_outputs;
use macropage\LaravelSchedulerWatcher\Models\job_events;
use macropage\LaravelSchedulerWatcher\Models\jobs;
use Illuminate\Console\Command;

class SchedulerWatcherCommandCheckLastRun extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scheduler-watcher:checklastevent {jobMD5} {--noansi}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get common infos of Jobs';

    public function handle(): string|int|null
    {
        $job = jobs::whereJobMd5($this->argument('jobMD5'))->first();
        if (!$job) {
            $this->echo('unable to find any job with your md5','error');
            return self::INVALID;
        }
        $last_job_events = job_events::whereJobeJobId($job->job_id)->orderByDesc('jobe_id')->limit(1)->get()->first();
        if (!$last_job_events) {
            $this->echo('no events found for this job','warn');
            return self::INVALID;
        }
        $job_output = job_event_outputs::whereJoboJobeId($last_job_events->jobe_id)->first('jobo_output');
        $output = "Last exitcode from job: ".$job->job_name.': ['.$last_job_events->jobe_exitcode.'] - last output: ';
        if ($job_output) {
            if ($this->option('noansi')) {
                /**
                 * @see https://stackoverflow.com/a/40731340/1092858
                 */
                $output  .= preg_replace('#\\x1b[[][^A-Za-z]*[A-Za-z]#', '', $job_output->jobo_output);
            } else {
                $output .= "\n\n".Color::LIGHT_GREEN.$job_output->jobo_output.Color::RESET."\n\n\n";
            }
        }
        $this->echo($output,null,$last_job_events->jobe_exitcode);
        return $last_job_events->jobe_exitcode;
    }

    private function echo($str, ?string $type=null, int $exitcode=0):void {
        if ($this->option('noansi')) {
            echo $str;
        } else {
            $type = match ($exitcode) {
                1       => 'warn',
                0       => 'info',
                default => 'error',
            };
            $this->$type($str);
        }
    }
}
