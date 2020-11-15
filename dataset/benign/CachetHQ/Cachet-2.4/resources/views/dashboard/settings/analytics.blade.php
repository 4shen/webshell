@extends('layout.dashboard')

@section('content')
<div class="content-panel">
    @includeWhen(isset($subMenu), 'dashboard.partials.sub-sidebar')
    <div class="content-wrapper">
        <div class="header sub-header" id="application-setup">
            <span class="uppercase">
                {{ trans('dashboard.settings.analytics.analytics') }}
            </span>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <form id="settings-form" name="SettingsForm" class="form-vertical" role="form" action="{{ cachet_route('dashboard.settings', [], 'post') }}" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    @include('partials.errors')
                    <fieldset>
                        <div class="row">
                            <div class="col-xs-12">
                                <div class="form-group">
                                    <label>{{ trans('forms.settings.analytics.analytics_google') }}</label>
                                    <input type="text" name="app_analytics" class="form-control" value="{{ $appAnalytics }}" placeholder="UA-12345-12">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12">
                                <div class="form-group">
                                    <label>{{ trans('forms.settings.analytics.analytics_gosquared') }}</label>
                                    <input type="text" name="app_analytics_go_squared" class="form-control" value="{{ $appAnalyticsGoSquared }}" placeholder="GSN-12345-A">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12">
                                <div class="form-group">
                                    <label>{{ trans('forms.settings.analytics.analytics_piwik_url') }}</label>
                                    <input type="text" name="app_analytics_piwik_url" class="form-control" value="{{ $appAnalyticsPiwikUrl }}" placeholder="{{ trans('forms.settings.analytics.analytics_piwik_url') }}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12">
                                <div class="form-group">
                                    <label>{{ trans('forms.settings.analytics.analytics_piwik_siteid') }}</label>
                                    <input type="number" min="1" max="100" name="app_analytics_piwik_site_id" class="form-control" value="{{ $appAnalyticsPiwikSiteId }}" placeholder="{{ trans('forms.settings.analytics.analytics_piwik_siteid') }}">
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <div class="row">
                        <div class="col-xs-12">
                            <div class="form-group">
                                <button type="submit" class="btn btn-success">{{ trans('forms.save') }}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@stop
