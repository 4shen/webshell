@extends('layout.dashboard')

@section('content')
<div class="header">
    <div class="sidebar-toggler visible-xs">
        <i class="ion ion-navicon"></i>
    </div>
    <span class="uppercase">
        <i class="ion ion-ios-email-outline"></i> {{ trans('dashboard.subscribers.subscribers') }}
    </span>
</div>
<div class="content-wrapper">
    <div class="row">
        <div class="col-sm-12">
        @include('partials.errors')
        <form name="SubscriberForm" class="form-vertical" role="form" action="{{ cachet_route('dashboard.subscribers.create', [], 'post') }}" method="POST">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <fieldset>
            <div class="form-group">
                <label>{{ trans('forms.user.email') }}</label>
                <textarea rows="5" class="form-control" name="email" required placeholder="{{ trans('forms.user.email') }}">{{ Binput::old('email') }}</textarea>
                <span class="help-block">{{ trans('dashboard.subscribers.add.help') }}</span>
            </div>
            </fieldset>

            <div class="form-group">
                <div class="btn-group">
                    <button type="submit" class="btn btn-success">{{ trans('forms.add') }}</button>
                    <a class="btn btn-default" href="{{ cachet_route('dashboard.subscribers') }}">{{ trans('forms.cancel') }}</a>
                </div>
            </div>
        </form>
        </div>
    </div>
</div>
@stop
