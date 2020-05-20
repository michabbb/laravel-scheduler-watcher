<?php

namespace macropage\LaravelSchedulerWatcher;

use Illuminate\Support\ServiceProvider;
use macropage\LaravelSchedulerWatcher\Console\SchedulerWatcherCommandInfo;
use macropage\LaravelSchedulerWatcher\Console\SchedulerWatcherCommandCheckLastRun;

class LaravelSchedulerWatcherServiceProvider extends ServiceProvider {

    protected string $configPath = __DIR__ . '/../config/scheduler-watcher.php';
    protected string $migrationsPath = __DIR__ . '/../migrations';

    /**
     * Bootstrap the application services.
     */
    public function boot() {

        $this->loadMigrationsFrom($this->migrationsPath);

        if ($this->app->runningInConsole()) {
            $this->publishes([
                                 $this->configPath => config_path('laravel-scheduler-watcher.php'),
                             ], 'config');
            $this->commands([
                                SchedulerWatcherCommandInfo::class,
                                SchedulerWatcherCommandCheckLastRun::class
                            ]);
        }
    }

    /**
     * Register the application services.
     */
    public function register() {
        $this->mergeConfigFrom($this->configPath, 'scheduler-watcher');
    }
}
