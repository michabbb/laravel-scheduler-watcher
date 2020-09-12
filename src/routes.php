<?php

use macropage\LaravelSchedulerWatcher\Models\job_events;
use \Illuminate\Http\Request;

Route::get('scheduler-watcher', static function () {
    $last_job_events = job_events::where('jobe_exitcode', '>', 0)->with('job')->with('jobEventOutputs')->get();

    return view('LaravelSchedulerWatcher::overview', [
        'job_events' => $last_job_events,
        'converter'  => new \SensioLabs\AnsiConverter\AnsiToHtmlConverter()
    ]);
});

Route::post('scheduler-watcher', static function (Request $request) {
    $job_event = job_events::whereJobeId($request->get('jobe_id'))->get()->first();
    if (!$job_event) {
        abort(404, 'unknown job event id');
    }

    $job_event->jobe_exitcode = 0;
    $job_event->save();

    $last_job_events = job_events::where('jobe_exitcode', '>', 0)->with('job')->with('jobEventOutputs')->get();

    return view('LaravelSchedulerWatcher::overview', [
        'job_events' => $last_job_events,
        'converter'  => new \SensioLabs\AnsiConverter\AnsiToHtmlConverter()
    ]);
});
