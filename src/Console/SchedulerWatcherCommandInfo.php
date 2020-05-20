<?php

namespace macropage\LaravelSchedulerWatcher\Console;

use Codedungeon\PHPCliColors\Color;
use macropage\LaravelSchedulerWatcher\Models\job_event_outputs;
use macropage\LaravelSchedulerWatcher\Models\job_events;
use macropage\LaravelSchedulerWatcher\Models\jobs;
use AsciiTable\Builder;
use Illuminate\Console\Command;

class SchedulerWatcherCommandInfo extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scheduler-watcher:info {jobMD5} {--last-output}';

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
            $this->alert('unable to find any job with your md5');
            return 1;
        }
        $last_job_events = job_events::whereJobeJobId($job->job_id)->orderByDesc('jobe_id')->limit(10)->get();
        if (!$last_job_events) {
            $this->warn('no events found for this job');
            return 0;
        }
        /**
         * Job Info
         */
        $builder = new Builder();
        $builder->setTitle(Color::LIGHT_GREEN.'Job Info: '.$job->job_name.'  '.Color::RESET.'-  '.Color::GREEN.$job->job_command.Color::RESET);
        $builder->addRow((array)$job->getAttributes());
        echo "\n\n".$builder->renderTable()."\n\n";
        /**
         * Last events
         */
        $builder = new Builder();
        $builder->setTitle(Color::LIGHT_GREEN.'Last events'.Color::RESET);
        $last_job_event_id = 0;
        foreach ($last_job_events as $job_event) {
            if (!$last_job_event_id) {
                $last_job_event_id = $job_event->jobe_id;
            }
            $builder->addRow((array)$job_event->getAttributes());
        }
        echo $builder->renderTable()."\n\n";
        /**
         * Last event output
         */
        if ($this->option('last-output')) {
            $job_output = job_event_outputs::whereJoboJobeId($last_job_event_id)->first('jobo_output');
            if (!$job_output) {
                $this->warn('no output found for the last event');
                return 0;
            }
            $this->info('Last output for event jobe_id '.$last_job_event_id.':');
            echo Color::LIGHT_GREEN.$job_output->jobo_output.Color::RESET."\n\n\n";
        }
        return 0;
    }
}
