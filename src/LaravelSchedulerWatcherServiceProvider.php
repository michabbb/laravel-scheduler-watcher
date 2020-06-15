<?php

namespace macropage\LaravelSchedulerWatcher;

use Illuminate\Support\ServiceProvider;
use macropage\LaravelSchedulerWatcher\Console\SchedulerWatcherCommandCleanUp;
use macropage\LaravelSchedulerWatcher\Console\SchedulerWatcherCommandCleanUpAll;
use macropage\LaravelSchedulerWatcher\Console\SchedulerWatcherCommandInfo;
use macropage\LaravelSchedulerWatcher\Console\SchedulerWatcherCommandCheckLastRun;

class LaravelSchedulerWatcherServiceProvider extends ServiceProvider {

    protected $configPath = __DIR__ . '/../config/scheduler-watcher.php';
    protected $migrationsPath = __DIR__ . '/../migrations';

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
                                SchedulerWatcherCommandCheckLastRun::class,
                                SchedulerWatcherCommandCleanUp::class,
                                SchedulerWatcherCommandCleanUpAll::class
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
