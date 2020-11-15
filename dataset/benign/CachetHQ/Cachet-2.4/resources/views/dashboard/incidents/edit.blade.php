@extends('layout.dashboard')

@section('content')
<div class="header">
    <div class="sidebar-toggler visible-xs">
        <i class="ion ion-navicon"></i>
    </div>
    <span class="uppercase">
        <i class="ion ion-ios-information-outline"></i> {{ trans('dashboard.incidents.incidents') }}
    </span>
    &gt; <small>{{ trans('dashboard.incidents.edit.title') }}</small>
</div>
<div class="content-wrapper">
    <div class="row">
        <div class="col-md-12">
            @if(!$notificationsEnabled)
                <div class="alert alert-info" role="alert">
                    {{ trans('forms.incidents.notify_disabled') }}
                </div>
            @endif
            @include('partials.errors')
            <form class="form-vertical" name="IncidentForm" role="form" method="POST" autocomplete="off">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <fieldset>
                    <div class="form-group">
                        <label for="incident-name">{{ trans('forms.incidents.name') }}</label>
                        <input type="text" class="form-control" name="name" id="incident-name" required value="{{$incident->name}}" placeholder="{{ trans('forms.incidents.name') }}">
                    </div>
                    <div class="form-group">
                        <label for="incident-name">{{ trans('forms.incidents.status') }}</label><br>
                        <label class="radio-inline">
                            <input type="radio" name="status" value="1" {{ ($incident->status == 1) ? "checked='checked'" : "" }}>
                            <i class="ion ion-flag"></i>
                            {{ trans('cachet.incidents.status')[1] }}
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="status" value="2" {{ ($incident->status == 2) ? "checked='checked'" : "" }}>
                            <i class="ion ion-alert-circled"></i>
                            {{ trans('cachet.incidents.status')[2] }}
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="status" value="3" {{ ($incident->status == 3) ? "checked='checked'" : "" }}>
                            <i class="ion ion-eye"></i>
                            {{ trans('cachet.incidents.status')[3] }}
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="status" value="4" {{ ($incident->status == 4) ? "checked='checked'" : "" }}>
                            <i class="ion ion-checkmark"></i>
                            {{ trans('cachet.incidents.status')[4] }}
                        </label>
                    </div>
                    @if($incident->component)
                    <div class="form-group hidden" id="component-status">
                        <input type="hidden" name="component_id" value="{{ $incident->component->id }}">
                        <div class="panel panel-default">
                            <div class="panel-body">
                                <div class="radio-items">
                                    @foreach(trans('cachet.components.status') as $statusID => $status)
                                    <div class="radio-inline">
                                        <label>
                                            <input type="radio" name="component_status" value="{{ $statusID }}">
                                            {{ $status }}
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    <div class="form-group">
                        <label for="incident-visibility">{{ trans('forms.incidents.visibility') }}</label>
                        <select name="visible" id="incident-visibility" class="form-control">
                            <option value="1" {{ $incident->visible === 1 ? 'selected' : null }}>{{ trans('forms.incidents.public') }}</option>
                            <option value="0" {{ $incident->visible === 0 ? 'selected' : null }}>{{ trans('forms.incidents.logged_in_only') }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="incident-stick">{{ trans('forms.incidents.stick_status') }}</label>
                        <select name="stickied" id="incident-stick" class="form-control">
                            <option value="1" {{ $incident->stickied ? 'selected' : null }}>{{ trans('forms.incidents.stickied') }}</option>
                            <option value="0" {{ !$incident->stickied ? 'selected' : null }}>{{ trans('forms.incidents.not_stickied') }}</option>
                        </select>
                    </div>
                    @if($incident->component)
                    <div class="form-group" id="component-status">
                        <div class="panel panel-default">
                            <div class="panel-heading"><strong>{{ $incident->component->name }}</strong></div>
                            <div class="panel-body">
                                <div class="radio-items">
                                    @foreach(trans('cachet.components.status') as $statusID => $status)
                                    <div class="radio-inline">
                                        <label>
                                            <input type="radio" name="component_status" value="{{ $statusID }}" {{ $incident->component->status == $statusID ? "checked='checked'" : "" }}>
                                            {{ $status }}
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    <div class="form-group">
                        <label>{{ trans('forms.incidents.message') }}</label>
                        <div class="markdown-control">
                            <textarea name="message" class="form-control autosize" rows="5" required>{{ $incident->message }}</textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>{{ trans('forms.incidents.occurred_at') }}</label> <small class="text-muted">{{ trans('forms.optional') }}</small>
                        <input type="text" name="occurred_at" class="form-control flatpickr-time" data-date-format="Y-m-d H:i" value="{{ $incident->occurred_at_datetimepicker }}" placeholder="{{ trans('forms.optional') }}">
                    </div>
                    <div class="form-group">
                        <label>{{ trans('forms.seo.title') }}</label> <small class="text-muted">{{ trans('forms.optional') }}</small>
                        <input type="text" name="seo[title]" class="form-control"  value="{{ array_get($incident->meta, 'seo.title', '')  }}">
                    </div>
                    <div class="form-group">
                        <label>{{ trans('forms.seo.description') }}</label> <small class="text-muted">{{ trans('forms.optional') }}</small>
                        <input type="text" name="seo[description]" class="form-control" value="{{ array_get($incident->meta, 'seo.description', '') }}">
                    </div>
                </fieldset>

                <div class="form-group">
                    <div class="btn-group">
                        <button type="submit" class="btn btn-success">{{ trans('forms.update') }}</button>
                        <a class="btn btn-default" href="{{ cachet_route('dashboard.incidents') }}">{{ trans('forms.cancel') }}</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@stop
