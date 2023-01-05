<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToSchedulerWatcherJobEventOutputsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::connection(config('laravel-scheduler-watcher.mysql_connection'))->table(config('scheduler-watcher.table_prefix') . 'job_event_outputs', function (Blueprint $table) {
            $table->foreign('jobo_jobe_id', 'FK_job_event_outputs_job_events_jobe_id')->references('jobe_id')->on(config('scheduler-watcher.table_prefix') . 'job_events')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::connection(config('laravel-scheduler-watcher.mysql_connection'))->table(config('scheduler-watcher.table_prefix') . 'job_event_outputs', function (Blueprint $table) {
            $table->dropForeign('FK_job_event_outputs_job_events_jobe_id');
        });
    }

}
