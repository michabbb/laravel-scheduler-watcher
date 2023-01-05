<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSchedulerWatcherJobsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::connection(config('laravel-scheduler-watcher.mysql_connection'))->create(config('scheduler-watcher.table_prefix') . 'jobs', function (Blueprint $table) {
            $table->integer('job_id', true);
            $table->char('job_md5', 32)->unique('UK_jobs_job_md5');
            $table->string('job_name');
            $table->string('job_command')->nullable();
            $table->dateTime('job_db_created')->nullable();
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::connection(config('laravel-scheduler-watcher.mysql_connection'))->drop(config('scheduler-watcher.table_prefix') . 'jobs');
    }

}
