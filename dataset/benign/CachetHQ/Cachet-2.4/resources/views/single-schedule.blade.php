@extends('layout.master')

@section('title', $schedule->name.' | '.$siteTitle)

@section('description', trans('cachet.meta.description.schedule', ['name' => $schedule->name, 'startDate' => $schedule->scheduled_at_formatted]))

@section('bodyClass', 'no-padding')

@section('outer-content')
@include('partials.nav')
@stop

@section('content')
<h1>{{ $schedule->name }}</h1>

<div class="timeline">
    <div class="content-wrapper">
        <div class="moment first">
            <div class="row event clearfix">
                <div class="col-sm-1">
                    <div class="status-icon status-{{ $schedule->status }}" data-toggle="tooltip" title="{{ $schedule->human_status }}" data-placement="left">
                        <i class="icon ion-android-calendar"></i>
                    </div>
                </div>
                <div class="col-xs-10 col-xs-offset-2 col-sm-11 col-sm-offset-0">
                    <div class="panel panel-message incident">
                        <div class="panel-heading">
                            <strong>{{ $schedule->name }}</strong>{{ trans("cachet.incidents.scheduled_at", ["timestamp" => $schedule->scheduled_at_diff]) }}
                        </div>
                        <div class="panel-body">
                            {!! $schedule->formatted_message !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('bottom-content')
@include('partials.footer')
@stop
