<?php

use Illuminate\Http\Request;
use macropage\LaravelSchedulerWatcher\Models\job_events;
use SensioLabs\AnsiConverter\AnsiToHtmlConverter;

$getShowUnfinishedAfterMinutes = static function (): int {
    return max(1, (int) config(
        'laravel-scheduler-watcher.show_unfinished_after_minutes',
        config('scheduler-watcher.show_unfinished_after_minutes', 360)
    ));
};

$getVisibleJobEvents = static function () use ($getShowUnfinishedAfterMinutes) {
    $showUnfinishedAfterMinutes = $getShowUnfinishedAfterMinutes();
    $jobEventsTable             = (new job_events())->getTable();
    $qualifiedStartColumn       = '`' . str_replace('`', '``', $jobEventsTable) . '`.`jobe_start`';

    return job_events::where(static function ($query) use ($qualifiedStartColumn, $showUnfinishedAfterMinutes) {
        $query->where('jobe_exitcode', '>', 0)
              ->orWhere(static function ($query) use ($qualifiedStartColumn, $showUnfinishedAfterMinutes) {
                  $query->where(static function ($query) {
                      $query->whereNull('jobe_end')
                            ->orWhereNull('jobe_exitcode');
                  })->whereHas('job', static function ($query) use ($qualifiedStartColumn, $showUnfinishedAfterMinutes) {
	                      $query->whereRaw(
	                          'TIMESTAMPDIFF(MINUTE, ' . $qualifiedStartColumn . ', NOW()) > COALESCE(NULLIF(job_max_runtime_minutes, 0), ?)',
	                          [$showUnfinishedAfterMinutes]
	                      );
                  });
              });
    })->with('job')->with('jobEventOutputs')->orderByDesc('jobe_id')->get();
};

Route::get('scheduler-watcher', static function () use ($getVisibleJobEvents, $getShowUnfinishedAfterMinutes) {
    return view('LaravelSchedulerWatcher::overview', [
        'job_events'                    => $getVisibleJobEvents(),
        'converter'                     => new AnsiToHtmlConverter(),
        'show_unfinished_after_minutes' => $getShowUnfinishedAfterMinutes()
    ]);
});

Route::post('scheduler-watcher', static function (Request $request) use ($getVisibleJobEvents, $getShowUnfinishedAfterMinutes) {
    $job_event = job_events::whereJobeId($request->get('jobe_id'))->get()->first();
    if (!$job_event) {
        abort(404, 'unknown job event id');
    }

    $job_event->jobe_exitcode = 0;
    $job_event->save();

    return view('LaravelSchedulerWatcher::overview', [
        'job_events'                    => $getVisibleJobEvents(),
        'converter'                     => new AnsiToHtmlConverter(),
        'show_unfinished_after_minutes' => $getShowUnfinishedAfterMinutes()
    ]);
});
