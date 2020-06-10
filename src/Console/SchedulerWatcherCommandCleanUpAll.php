<?php

namespace macropage\LaravelSchedulerWatcher\Console;

use macropage\LaravelSchedulerWatcher\Models\job_events;
use macropage\LaravelSchedulerWatcher\Models\jobs;
use Illuminate\Console\Command;

class SchedulerWatcherCommandCleanUpAll extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scheduler-watcher:cleanup-all {--keep=10}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean table job_events and keep last X entries of every existing job';

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
     * @throws \Exception
     * @noinspection DuplicatedCode
     */
    public function handle() {
        $jobs = jobs::all();
        if ($jobs->isEmpty()) {
            $this->alert('no jobs found!');
            return 1;
        }
        foreach ($jobs as $job) {
            $last_job_events = job_events::whereJobeJobId($job->job_id)->orderByDesc('jobe_id')->limit((int)$this->option('keep'))->get();
            if ($last_job_events->isEmpty()) {
                $this->warn('no events found for this job');

                return 0;
            }
            $events_we_keep = [];
            foreach ($last_job_events as $job_event) {
                $events_we_keep[] = $job_event->jobe_id;
            }
            $count_deleted = job_events::whereJobeJobId($job->job_id)->whereNotIn('jobe_id', $events_we_keep)->delete();
            $this->info('deleted ' . $count_deleted . ' old entries of: ' . $job->job_name);
        }
        return 0;
    }
}
