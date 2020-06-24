<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSchedulerWatcherJobEventOutputsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void {
        Schema::connection('mysql_scheduler')->create(config('scheduler-watcher.table_prefix') . 'job_event_outputs', function (Blueprint $table) {
            $table->integer('jobo_id', true);
            $table->integer('jobo_jobe_id')->nullable()->index('FK_job_event_outputs_job_events_jobe_id');
            $table->text('jobo_output')->nullable();
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void {
        Schema::connection('mysql_scheduler')->drop(config('scheduler-watcher.table_prefix') . 'job_event_outputs');
    }

}
