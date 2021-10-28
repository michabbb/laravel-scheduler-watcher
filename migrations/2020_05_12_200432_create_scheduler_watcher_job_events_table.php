<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSchedulerWatcherJobEventsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void {
        Schema::connection('mysql_scheduler')->create(config('scheduler-watcher.table_prefix') . 'job_events', function (Blueprint $table) {
            $table->integer('jobe_id', true);
            $table->integer('jobe_job_id')->nullable()->index('FK_job_events_jobs_job_id');
            $table->dateTime('jobe_start')->nullable();
            $table->dateTime('jobe_end')->nullable();
            $table->float('jobe_duration', 20, 13)->nullable();
            $table->tinyInteger('jobe_exitcode')->nullable();
            $table->dateTime('jobe_db_created')->nullable();
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void {
        Schema::connection('mysql_scheduler')->drop(config('scheduler-watcher.table_prefix') . 'job_events');
    }

}
