<?php

namespace Macropage\LaravelSchedulerWatcher\Tests;

use Orchestra\Testbench\TestCase;
use Macropage\LaravelSchedulerWatcher\LaravelSchedulerWatcherServiceProvider;

class ExampleTest extends TestCase
{

    protected function getPackageProviders($app)
    {
        return [LaravelSchedulerWatcherServiceProvider::class];
    }
    
    /** @test */
    public function true_is_true()
    {
        $this->assertTrue(true);
    }
}
