<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateJobEventOutputsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::connection('mysql_scheduler')->create('job_event_outputs', function(Blueprint $table)
		{
			$table->integer('jobo_id', true);
			$table->integer('jobo_jobe_id')->nullable()->index('FK_job_event_outputs_job_events_jobe_id');
			$table->text('jobo_output', 65535)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::connection('mysql_scheduler')->drop('job_event_outputs');
	}

}
