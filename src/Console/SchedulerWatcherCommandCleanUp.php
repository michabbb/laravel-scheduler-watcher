<?php

namespace macropage\LaravelSchedulerWatcher\Console;

use macropage\LaravelSchedulerWatcher\Models\job_events;
use macropage\LaravelSchedulerWatcher\Models\jobs;
use Illuminate\Console\Command;

class SchedulerWatcherCommandCleanUp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scheduler-watcher:cleanup {jobMD5} {--keep=10}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean table job_events and keep last X entries';


    public function handle(): int
    {
        $job = jobs::whereJobMd5($this->argument('jobMD5'))->first();
        if (!$job) {
            $this->alert('unable to find any job with your md5');
            return 1;
        }
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
        return 0;
    }
}
