<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToJobEventsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::connection('mysql_scheduler')->table(config('scheduler-watcher.table_prefix').'job_events', function(Blueprint $table)
		{
			$table->foreign('jobe_job_id', 'FK_job_events_jobs_job_id')->references('job_id')->on(config('scheduler-watcher.table_prefix').'jobs')->onUpdate('RESTRICT')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::connection('mysql_scheduler')->table(config('scheduler-watcher.table_prefix').'job_events', function(Blueprint $table)
		{
			$table->dropForeign('FK_job_events_jobs_job_id');
		});
	}

}
