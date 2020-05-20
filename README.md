# Monitor your laravel crons like a pro

Log your scheduled commands with start, end, duration, exitcode and output in a seperate database.  
Share a custom Mutex between Scheduler and Artisan-Command to identify each call with all parameters and options.

## Why?

Because you should!  
Laravel offers some nice [hooks](https://laravel.com/docs/7.x/scheduling#task-hooks) like onFailure but I am totally missing a solution to catch  
all important informations of the job-execution itself, like start, stop, duration and exitcode.  
My personal goal is to monitor jobs with the following criteria:

- notify me if a job did not run in the last X days/hours/minutes
- notify me if a job ended with exitcode>0
- notify me if a jobs duration takes much longer as it should
- monitor something from a central point even in distributed environments
- monitor individual jobs with my good old nagios (yeah, don´t blame me)

## Installation

You can install the package via composer:

```bash
composer require macropage/laravel-scheduler-watcher
```

Publish config & migration using `php artisan vendor:publish --provider="macropage\LaravelSchedulerWatcher\LaravelSchedulerWatcherServiceProvider"`  
Set `mysql_connection` in: `app/config/scheduler-watcher.php`   
Create mysql tables: `php artisan migrate`

## Usage
Write your cron-jobs as usal, but use the "description" to control your logging:

- log
- nooutput
- force

**Example 1:**
```php
$schedule->command('dummy:test blabla -c')->everyMinute()->description('Call dummy test');
```
Nothing happens, no logging, laravel default... (i hope so)

**Example 2:**
```php
$schedule->command('dummy:test blabla -c')->everyMinute()->description('Call dummy test [log]');
```
Command gets logged into DB, but doesn´t run again, if last exitcode>0

**Example 3:**
```php
$schedule->command('dummy:test blabla -c')->everyMinute()->description('Call dummy test [log,nooutput]');
```
Command gets logged, but without output. You can use this to keep the logging-DB small.

**Example 4:**
```php
$schedule->command('dummy:test blabla -c')->everyMinute()->description('Call dummy test [log,nooutput,force]');
```
The switch "force" lets run your command, ignoring last exitcode. **be careful: this can spam your DB.**  
Personally I would use "force" only with "nooutput".

#### Kernel.php
To use the logging, you have to include the Trait `LaravelSchedulerWatcher` into your `app\Console\Kernel.php`
```php
use macropage\LaravelSchedulerWatcher\LaravelSchedulerWatcher;

class Kernel extends ConsoleKernel {

  use LaravelSchedulerWatcher;

  protected function schedule(Schedule $schedule): void {
    $schedule->command('dummy:test blabla -c')->everyMinute()->description('Call dummy test');
    $this->monitor($schedule);
  }
}
```
Inside the `schedule` function, call the monitor. This is the place where all the magic happens ;)

#### inside your artisan-command
The Scheduler needs to be able to get the `CustomMutex` generated from your Artisan-Command.  
Because I don´t know (for now) how the Scheduler can talk directly to an Artisan-Command,  
you have to make some very little modifications there, too.
```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use macropage\LaravelSchedulerWatcher\LaravelSchedulerCustomMutex;

class dummy extends Command {

    use LaravelSchedulerCustomMutex;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = '';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        $this->setSignature('dummy:test {blabla} {--c|check} {--t|test}');
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        if ($this->checkCustomMutex()) {
            return 0;
        }
        // your regular code
    }
}
```
The things you have to do here:
- `use LaravelSchedulerCustomMutex;`
- set your `Signature` with the helper `setSignature` instead directly (because --mutex gets added automatically)
- inside `handle` add the `checkCustomMutex`

with that, you are good to start.



## Logging to File
The last Output-File (the file that captured the output of your job) will be written to `/tmp/<mutex>.scheduler.output.log`.  
`<mutex>` = the custom mutex generated based on your command + all arguments and parameters.  
The last output-logfile **does not get deleted**, this is intentional.  
I thought it´s a good idea so you always have quick access to the last output for debugging without looking into the DB.

## Mutex
In case you never heard "mutex", you might want to read [this](https://divinglaravel.com/preventing-scheduled-jobs-overlapping).  
The `<mutex>` **is not** the same Mutex laravel uses for handling `withoutOverlapping`.  
`withoutOverlapping` is still handled by laravel itself. The custom Mutex of this package is only used to identify your commands  
with a simple md5-hash.
The Mutex does **NOT** contain the crontab info `* * * * 5` itself, because from my point of view,  
it´s not important "when" something is running, it´s more important "what" is running. And for this, you need to know the  
command itself, the arguments and options. Check the function `getCustomMutex` in case it´s not clear ;)

## Helper Artisan Commands
### scheduler-watcher:info
There are currently two helper command:

`artisan scheduler-watcher:info <mutex> --last-output`  
Example: `artisan scheduler-watcher:info 4a1a273959acfc335fb3fd01a069bec9 --last-output`  
This helper gives you a quick overview of the last events and, if you want, the last output of the last run:
```text
Job Info: Command description  -  '/usr/local/bin/php' 'artisan' dummy:test blabla -c
┌────────┬──────────────────────────────────┬─────────────────────┬─────────────────────────────────────────────────────┬─────────────────────┐
│ job_id │ job_md5                          │ job_name            │ job_command                                         │ job_db_created      │
├────────┼──────────────────────────────────┼─────────────────────┼─────────────────────────────────────────────────────┼─────────────────────┤
│      5 │ 4a1a273959acfc335fb3fd01a069bec9 │ Command description │ '/usr/local/bin/php' 'artisan' dummy:test blabla -c │ 2020-05-14 16:09:37 │
└────────┴──────────────────────────────────┴─────────────────────┴─────────────────────────────────────────────────────┴─────────────────────┘

                                                   Last events
┌─────────┬─────────────┬─────────────────────┬─────────────────────┬─────────────────┬───────────────┬─────────────────────┐
│ jobe_id │ jobe_job_id │ jobe_start          │ jobe_end            │ jobe_duration   │ jobe_exitcode │ jobe_db_created     │
├─────────┼─────────────┼─────────────────────┼─────────────────────┼─────────────────┼───────────────┼─────────────────────┤
│      17 │           5 │ 2020-05-14 16:34:39 │ 2020-05-14 16:34:42 │  2.320925951004 │             1 │ 2020-05-14 16:34:48 │
│      16 │           5 │ 2020-05-14 16:33:52 │ 2020-05-14 16:33:54 │ 2.0258629322052 │             1 │ 2020-05-14 16:34:00 │
│      15 │           5 │ 2020-05-14 16:31:07 │ 2020-05-14 16:31:09 │ 2.0691630840302 │             1 │ 2020-05-14 16:31:16 │
│      14 │           5 │ 2020-05-14 16:30:53 │ 2020-05-14 16:30:55 │ 1.9885129928589 │             1 │ 2020-05-14 16:31:01 │
│      13 │           5 │ 2020-05-14 16:30:35 │ 2020-05-14 16:30:38 │  2.839812040329 │             1 │ 2020-05-14 16:30:44 │
│      12 │           5 │ 2020-05-14 16:15:07 │ 2020-05-14 16:15:10 │ 2.8682150840759 │             1 │ 2020-05-14 16:15:16 │
│      11 │           5 │ 2020-05-14 16:10:19 │ 2020-05-14 16:10:21 │  1.945338010788 │             0 │ 2020-05-14 16:10:27 │
│      10 │           5 │ 2020-05-14 16:09:29 │ 2020-05-14 16:09:31 │ 1.9296040534973 │             0 │ 2020-05-14 16:09:37 │
└─────────┴─────────────┴─────────────────────┴─────────────────────┴─────────────────┴───────────────┴─────────────────────┘

Last output for event jobe_id 17:
yeahh.....4a1a273959acfc335fb3fd01a069bec9
```
hint: `job_md5` is my custome-mutex i am talking all along ;)

### scheduler-watcher:checklastevent
`artisan scheduler-watcher:checklastevent <mutex>`  
Exmaple: `artisan scheduler-watcher:checklastevent 4a1a273959acfc335fb3fd01a069bec9`
```text
Last exitcode from job: Command description: [1] - last output: yeah.....
```  
This Helper only returns the last output of the last run of your job and exits with the same exitcode of this last run:
```bash
echo $?
1
```
With that you are able to build checks for your monitoring system, for instance [Nagios](https://assets.nagios.com/downloads/nagioscore/docs/nagioscore/3/en/pluginapi.html).  
Of course you can use the data directly from the DB, this is just something I created for myself ;)  


## FAQ

**is there any webinterface?**   
no. maybe in the future.

**are notifications included in this package?**  
no, it´s up to you what you do with your logging.  
there are some helper artisan commands, that give you an idea, how you  
can build your own rules to notify you in case something is not as you expect it to be.

**can i monitor none artisan jobs, too?**  
yes, but currently there is no option to stop logging in case the last run failed, this is on my todo.

**when calling artisan commands: can i choose to not run the command, if last run failed?**  
yes, the last exitcode gets fetched from DB, if you want, that is optional.

**can i use a seperate database to log my cron?**  
of course.

**can i use a different database than mysql?**  
not yet tested, let me know.

**i don´t get it: why use a custom mutex and not the same like laravel internally uses?**  
1) in case i am not wrong here: the mutex only exists in context of the scheduler.  
if you want to work with the logging-db within an artisan-command that is not called by the scheduler, you don´t have any mutex.  
so you need to know the `job_md5` and there for you can call `getCustomMutex` within your artisan-command.  
with that every artisan command is able to check itself, for instance show last duration, last exitcode, whatever you want.
2) laravel does `'framework'.DIRECTORY_SEPARATOR.'schedule-'.sha1($this->expression.$this->command);` and I don´t want  
`$this->expresseion` (example: `* 0 * * 5`) part of this mutex. In case I am wrong and it makes sense because someone  
wants to identify two identical jobs running at different times, we can change this, I am open to that ;)

**how exactly do i montior my jobs now?**  
use the table `job_events`. with the information there, you can write your own selects to check:
- last time the job has been executed (jobe_start)
- last time the job has been executed successfully (jobe_start + jobe_exitcode)
- how long did the job take (jobe_duration)
 
if any of these informations do not fit into your personal range, send yourself a notification, that´s it.

**last exitcode was > 0 and i don´t want to use "force", how do i keep my jobs running after a problem has been solved?**  
yeah, good question. because there is currently no pid-file involved and the database is used to check if the "last run"  
was okay or not, you are forced to set the exitcode (jobe_exitcode) of your last run to "0". i know, it´s not perfect  
changing log-entries afterwards, but nothing is forever. you should clean your database anyway after a while.  
if you prefer having log-informations on a long term, i suggest to add new table `job_events_archiv` and a trigger  
that duplicates all entries. with that you don´t loose performance with the active table `job_events` and you are  
able to collect data for a long time, for instance you want to check the duration of your jobs over months. 

## Contributing

Help is appreciated :-)

## You need help?
_yes, you can hire me!_  
    
[![xing](https://i.imgur.com/V3RuEM7.png)](https://www.xing.com/profile/Michael_Bladowski/cv)
[![linkedin](https://i.imgur.com/UNH7YtM.png)](https://www.linkedin.com/in/macropage/)
[![twitter](https://i.imgur.com/iSv2xRb.png)](https://twitter.com/michabbb)


## Credits

- [Laravel Totem](https://github.com/codestudiohq/laravel-totem)
- [mirzabusatlic/laravel-schedule-monitor](https://github.com/mirzabusatlic/laravel-schedule-monitor)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).
