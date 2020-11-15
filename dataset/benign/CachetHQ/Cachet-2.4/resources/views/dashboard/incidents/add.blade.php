@extends('layout.dashboard')

@section('content')
<div class="header">
    <div class="sidebar-toggler visible-xs">
        <i class="ion ion-navicon"></i>
    </div>
    <span class="uppercase">
        <i class="ion ion-ios-information-outline"></i> {{ trans('dashboard.incidents.incidents') }}
    </span>
    &gt; <small>{{ trans('dashboard.incidents.add.title') }}</small>
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
            <report-incident inline-template>
                <form class="form-vertical" name="IncidentForm" role="form" method="POST" autocomplete="off">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <fieldset>
                        @if($incidentTemplates->count() > 0)
                        <div class="form-group">
                            <label for="template">{{ trans('forms.incidents.templates.template') }}</label>
                            <select class="form-control" name="template" v-model="template">
                                <option selected></option>
                                @foreach($incidentTemplates as $tpl)
                                <option value="{{ $tpl->slug }}">{{ $tpl->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        <div class="form-group">
                            <label for="incident-name">{{ trans('forms.incidents.name') }}</label>
                            <input type="text" class="form-control" name="name" id="incident-name" required value="{{ Binput::old('name') }}" placeholder="{{ trans('forms.incidents.name') }}" v-model="name">
                        </div>
                        <div class="form-group">
                            <label for="incident-name">{{ trans('forms.incidents.status') }}</label><br>
                            <label class="radio-inline">
                                <input type="radio" name="status" value="1" v-model="status">
                                <i class="ion ion-flag"></i>
                                {{ trans('cachet.incidents.status')[1] }}
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="status" value="2" v-model="status">
                                <i class="ion ion-alert-circled"></i>
                                {{ trans('cachet.incidents.status')[2] }}
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="status" value="3" v-model="status">
                                <i class="ion ion-eye"></i>
                                {{ trans('cachet.incidents.status')[3] }}
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="status" value="4" v-model="status">
                                <i class="ion ion-checkmark"></i>
                                {{ trans('cachet.incidents.status')[4] }}
                            </label>
                        </div>
                        <div class="form-group">
                            <label for="incident-name">{{ trans('forms.incidents.visibility') }}</label>
                            <select name="visible" class="form-control" v-model="visible">
                                <option value="1" selected>{{ trans('forms.incidents.public') }}</option>
                                <option value="0">{{ trans('forms.incidents.logged_in_only') }}</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="incident-name">{{ trans('forms.incidents.stick_status') }}</label>
                            <select name="stickied" class="form-control" v-model="sticky">
                                <option value="1">{{ trans('forms.incidents.stickied') }}</option>
                                <option value="0" selected>{{ trans('forms.incidents.not_stickied') }}</option>
                            </select>
                        </div>
                        @if(!$componentsInGroups->isEmpty() || !$componentsOutGroups->isEmpty())
                        <div class="form-group">
                            <label>{{ trans('forms.incidents.component') }}</label> <small class="text-muted">{{ trans('forms.optional') }}</small>
                            <select name="component_id" class="form-control" v-model="component.id">
                                <option value="" selected></option>
                                @foreach($componentsInGroups as $group)
                                <optgroup label="{{ $group->name }}">
                                    @foreach($group->components as $component)
                                    <option value="{{ $component->id }}">{!! $component->name !!}</option>
                                    @endforeach
                                </optgroup>
                                @endforeach
                                @foreach($componentsOutGroups as $component)
                                <option value="{{ $component->id }}">{!! $component->name !!}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        <div class="form-group" id="component-status" v-if="component.id">
                            <label>{{ trans('forms.incidents.component_status') }}</label>
                            <div class="panel panel-default">
                                <div class="panel-body">
                                    <div class="radio-items">
                                        @foreach(trans('cachet.components.status') as $statusID => $status)
                                        <div class="radio-inline">
                                            <label>
                                                <input type="radio" name="component_status" value="{{ $statusID }}" v-model="component.status">
                                                {{ $status }}
                                            </label>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>{{ trans('forms.incidents.message') }}</label>
                            <div class="markdown-control">
                                <textarea name="message" class="form-control autosize" rows="5" required v-model="message">{{ Binput::old('message') }}</textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>{{ trans('forms.incidents.occurred_at') }}</label> <small class="text-muted">{{ trans('forms.optional') }}</small>
                            <input type="text" name="occurred_at" class="form-control flatpickr-time" data-date-format="Y-m-d H:i" placeholder="{{ trans('forms.optional') }}">
                        </div>
                        @if($notificationsEnabled)
                        <input type="hidden" name="notify" value="0">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="notify" value="1" checked="{{ Binput::old('notify', 'checked') }}">
                                {{ trans('forms.incidents.notify_subscribers') }}
                            </label>
                        </div>
                        @endif
                        <div class="form-group">
                            <label>{{ trans('forms.seo.title') }}</label> <small class="text-muted">{{ trans('forms.optional') }}</small>
                            <input type="text" name="seo[title]" class="form-control" placeholder="{{ trans('forms.optional') }}">
                        </div>
                        <div class="form-group">
                            <label>{{ trans('forms.seo.description') }}</label> <small class="text-muted">{{ trans('forms.optional') }}</small>
                            <input type="text" name="seo[description]" class="form-control" placeholder="{{ trans('forms.optional') }}">
                        </div>
                    </fieldset>

                    <div class="form-group">
                        <div class="btn-group">
                            <button type="submit" class="btn btn-success">{{ trans('forms.add') }}</button>
                            <a class="btn btn-default" href="{{ cachet_route('dashboard.incidents') }}">{{ trans('forms.cancel') }}</a>
                        </div>
                    </div>
                </form>
            </report-incident>
        </div>
    </div>
</div>
@stop
