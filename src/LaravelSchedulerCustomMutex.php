<?php

namespace macropage\LaravelSchedulerWatcher;

use macropage\LaravelSchedulerWatcher\Models\job_events;

trait LaravelSchedulerCustomMutex {
    public function setSignature($signature): void {
        $this->signature = $signature . ' {--mutex} {--description} {--F|force}';
    }

    /*public function getCommandDescription(): bool {
        if ($this && $this->option('description')) {
            $this->info($this->description);

            return true;
        }

        return false;
    }*/

    public function lastRunWasNotOkay(): int {
        if ($this && !$this->option('force')) {
            $last_job_event = job_events::whereHas('job', function ($query) {
                $query->whereJobMd5($this->getCustomMutex());
            })->orderByDesc('jobe_id')->first('jobe_exitcode');

            return ($last_job_event && $last_job_event->jobe_exitcode);
        }
        return false;
    }

    public function getCustomMutex(): string {
        $CommandOptions = $this->options();
        unset($CommandOptions['mutex']);

        $LaravelDefaultOptions = $this->getApplication()->getDefinition()->getOptions();
        $onlyMyOptions         = array_diff_key($CommandOptions, $LaravelDefaultOptions);
        $arguments             = $this->arguments();
        ksort($arguments);
        ksort($onlyMyOptions);

        return md5(serialize([
                                 'name'      => $this->getName(),
                                 'arguments' => $arguments,
                                 'options'   => $onlyMyOptions
                             ]));
    }

    public function checkCustomMutex(): bool {
        if ($this && $this->option('mutex')) {
            $this->info($this->getCustomMutex());

            return true;
        }

        return false;
    }
}
