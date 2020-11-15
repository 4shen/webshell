@extends('layout.dashboard')

@section('content')
<div class="content-panel">
    @includeWhen(isset($subMenu), 'dashboard.partials.sub-sidebar')
    <div class="content-wrapper">
        <div class="header sub-header" id="application-setup">
            <span class="uppercase">
                {{ trans('dashboard.settings.credits.credits') }}
            </span>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <h4>Cachet</h4>

                <p>{!! trans('dashboard.settings.credits.license') !!}</p>

                <hr>

                <h4>{{ trans('dashboard.settings.credits.contributors') }}</h4>

                <p>{{ trans('dashboard.settings.credits.thank-you', ['count' => count($contributors)]) }}</p>

                <ul class="list-inline">
                    @foreach($contributors as $contributor)
                    <li>
                        <a href="{{ $contributor['site'] }}" target="_blank">
                            <img src="{{ $contributor['avatar'] }}" class="img-rounded img-responsive" title="{{ $contributor['name'] }}" data-toggle="tooltip" height="100" width="100">
                        </a>
                    </li>
                    @endforeach
                </ul>

                <hr>
            </div>
        </div>
    </div>
</div>
@stop
