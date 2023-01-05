<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{

    public function up(): void
    {
        DB::connection(config('laravel-scheduler-watcher.mysql_connection'))->unprepared('
                CREATE
                    TRIGGER tr_bi_jobs
                        BEFORE INSERT
                        ON jobs
                        FOR EACH ROW
                    BEGIN
                            SET NEW.job_db_created = NOW();
                    END');
        DB::connection(config('laravel-scheduler-watcher.mysql_connection'))->unprepared('
                CREATE
                    TRIGGER tr_bi_job_events
                        BEFORE INSERT
                        ON job_events
                        FOR EACH ROW
                    BEGIN
                            SET NEW.jobe_db_created = NOW();
                    END');
        DB::connection(config('laravel-scheduler-watcher.mysql_connection'))->unprepared('
                CREATE
                    TRIGGER tr_bu_job_events
                        BEFORE UPDATE
                        ON job_events
                        FOR EACH ROW
                    BEGIN
                            SET NEW.jobe_db_changed = NOW();
                    END');
        DB::connection(config('laravel-scheduler-watcher.mysql_connection'))->unprepared('
                CREATE
                    TRIGGER tr_bi_job_event_outputs
                        BEFORE INSERT
                        ON job_event_outputs
                        FOR EACH ROW
                    BEGIN
                            SET NEW.jobo_db_created = NOW();
                    END');
    }


    public function down(): void
    {
        DB::connection(config('laravel-scheduler-watcher.mysql_connection'))->unprepared('DROP TRIGGER tr_bi_jobs');
        DB::connection(config('laravel-scheduler-watcher.mysql_connection'))->unprepared('DROP TRIGGER tr_bi_job_events');
        DB::connection(config('laravel-scheduler-watcher.mysql_connection'))->unprepared('DROP TRIGGER tr_bu_job_events');
        DB::connection(config('laravel-scheduler-watcher.mysql_connection'))->unprepared('DROP TRIGGER tr_bi_job_event_outputs');
    }

};
