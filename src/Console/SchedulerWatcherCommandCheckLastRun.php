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

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $job = jobs::whereJobMd5($this->argument('jobMD5'))->first();
        if (!$job) {
            $this->echo('unable to find any job with your md5','error');
            return 2;
        }
        $last_job_events = job_events::whereJobeJobId($job->job_id)->orderByDesc('jobe_id')->limit(1)->get()->first();
        if (!$last_job_events) {
            $this->echo('no events found for this job','warn');
            return 2;
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

    /**
     * @param      $str
     * @param null $type
     * @param int  $exitcode
     */
    private function echo($str, $type=null, $exitcode=0):void {
        if ($this->option('noansi')) {
            echo $str;
        } else {
            switch ($exitcode) {
                case 1:
                    $type = 'warn';
                    breaK;
                case 0:
                    $type = 'info';
                    break;
                default:
                    $type = 'error';
                    break;
            }
            $this->$type($str);
        }
    }
}
