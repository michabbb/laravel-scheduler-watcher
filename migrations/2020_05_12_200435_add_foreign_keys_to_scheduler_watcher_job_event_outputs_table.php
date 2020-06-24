<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToSchedulerWatcherJobEventOutputsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::connection('mysql_scheduler')->table('job_event_outputs', function(Blueprint $table)
		{
			$table->foreign('jobo_jobe_id', 'FK_job_event_outputs_job_events_jobe_id')->references('jobe_id')->on('job_events')->onUpdate('RESTRICT')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::connection('mysql_scheduler')->table('job_event_outputs', function(Blueprint $table)
		{
			$table->dropForeign('FK_job_event_outputs_job_events_jobe_id');
		});
	}

}
