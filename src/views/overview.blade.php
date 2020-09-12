<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Laravel Scheduler Watcher</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"
          integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
</head>
<body>
<div id="app">
    <div class="container-xl pt-4 w-auto">
        <div class="card">
            <div class="card-header">
                Laravel Scheduler Watcher - Overview failed jobs
            </div>
            <div class="card-body">
                <form method="POST" action="/scheduler-watcher">
                    @csrf
                    <table class="table">
                        <thead>
                        <tr>
                            <th scope="col">Confirm Error</th>
                            <th scope="col">job_id</th>
                            <th scope="col">job_md5</th>
                            <th scope="col">job_name</th>
                            <th scope="col">job_command</th>
                            <th scope="col">jobe_start</th>
                            <th scope="col">jobe_end</th>
                            <th scope="col">jobe_duration</th>
                            <th scope="col">jobe_exitcode</th>
                            <th scope="col">last_output</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($job_events as $job_event)
                            <tr>
                                <td>
                                    <button name="jobe_id" type="submit"
                                            class="btn btn-success small text-center"
                                            value="{{$job_event->jobe_id}}">confirm
                                    </button>
                                </td>
                                <th scope="row">{{$job_event->job->job_id}}</th>
                                <td>{{$job_event->job->job_md5}}</td>
                                <td>{{$job_event->job->job_name}}</td>
                                <td>{{$job_event->job->job_command}}</td>
                                <td>{{$job_event->jobe_start}}</td>
                                <td>{{$job_event->jobe_end}}</td>
                                <td>{{$job_event->jobe_duration}}</td>
                                <td>{{$job_event->jobe_exitcode}}</td>
                                <td>
                                    <pre
                                        style="background-color: black; color: white">{!! $converter->convert($job_event->jobEventOutputs[0]->jobo_output) !!}</pre>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>
