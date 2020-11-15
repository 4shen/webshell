@extends('layout.clean')

@section('pageTitle', trans('setup.setup'))

@section('content')
<div class="setup-page">
    <div class="text-center">
        <img class="logo" height="90" src="{{ asset('img/cachet-logo.svg') }}" alt="{{ trans('setup.title') }}">
    </div>
    <div class="col-xs-12 col-xs-offset-0 col-sm-8 col-sm-offset-2">
        <div class="steps">
            <div class="step active">
                {{ trans('setup.env_setup') }}
                <span></span>
            </div>
            <div class="step">
                {{ trans('setup.status_page_setup') }}
                <span></span>
            </div>
            <div class="step">
                {{ trans("setup.admin_account") }}
                <span></span>
            </div>
            <div class="step">
                {{ trans("setup.complete_setup") }}
                <span></span>
            </div>
        </div>

        <div class="clearfix"></div>

        <setup inline-template>
            <form class="form-horizontal" name="SetupForm" method="POST" id="setup-form" role="form">
                <div class="step block-1">
                    <fieldset>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-xs-4">
                                    <label>{{ trans('forms.setup.cache_driver') }}</label>
                                    <select name="env[cache_driver]" class="form-control" required v-model="env.cache_driver">
                                        <option disabled>{{ trans('forms.setup.cache_driver') }}</option>
                                        @foreach($cacheDrivers as $driver => $driverName)
                                        <option value="{{ $driver }}" {{ Binput::old('env.cache_driver', $cacheConfig['driver']) == $driver ? "selected" : null }}>{{ $driverName }}</option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('env.cache_driver'))
                                    <span class="text-danger">{{ $errors->first('env.cache_driver') }}</span>
                                    @endif
                                </div>
                                <div class="col-xs-4">
                                    <label>{{ trans('forms.setup.queue_driver') }}</label>
                                    <select name="env[queue_driver]" class="form-control" required v-model="env.queue_driver">
                                        <option disabled>{{ trans('forms.setup.queue_driver') }}</option>
                                        @foreach($queueDrivers as $driver => $driverName)
                                        <option value="{{ $driver }}" {{ Binput::old('env.queue_driver', $queueConfig['driver']) == $driver ? "selected" : null }}>{{ $driverName }}</option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('env.queue_driver'))
                                    <span class="text-danger">{{ $errors->first('env.queue_driver') }}</span>
                                    @endif
                                </div>
                                <div class="col-xs-4">
                                    <label>{{ trans('forms.setup.session_driver') }}</label>
                                    <select name="env[session_driver]" class="form-control" required v-model="env.session_driver">
                                        <option disabled>{{ trans('forms.setup.session_driver') }}</option>
                                        @foreach($cacheDrivers as $driver => $driverName)
                                        <option value="{{ $driver }}" {{ Binput::old('env.session_driver', $sessionConfig['driver']) == $driver ? "selected" : null }}>{{ $driverName }}</option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('env.session_driver'))
                                    <span class="text-danger">{{ $errors->first('env.session_driver') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="form-group">
                            <label>{{ trans('forms.setup.mail_driver') }}</label>
                            <select name="env[mail_driver]" class="form-control" required v-model="env.mail_driver">
                                <option disabled>{{ trans('forms.setup.mail_driver') }}</option>
                                @foreach($mailDrivers as $driver => $driverName)
                                <option value="{{ $driver }}" {{ Binput::old('env.mail_driver', $mailConfig['driver']) == $driver ? "selected" : null }}>{{ $driverName }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('env.mail_driver'))
                            <span class="text-danger">{{ $errors->first('env.mail_driver') }}</span>
                            @endif
                        </div>
                        <div class="form-group">
                            <label>{{ trans('forms.setup.mail_host') }}</label>
                            <input type="text" class="form-control" name="env[mail_host]" value="{{ Binput::old('env.mail_host', $mailConfig['host']) }}" placeholder="{{ trans('forms.setup.mail_host') }}" :required="mail.requiresHost">
                            @if($errors->has('env.mail_host'))
                            <span class="text-danger">{{ $errors->first('env.mail_host') }}</span>
                            @endif
                        </div>
                        <div class="form-group">
                            <label>{{ trans('forms.setup.mail_address') }}</label>
                            <input type="text" class="form-control" name="env[mail_address]" value="{{ Binput::old('env.mail_address', $mailConfig['from']['address']) }}" placeholder="notifications@alt-three.com">
                            @if($errors->has('env.mail_address'))
                            <span class="text-danger">{{ $errors->first('env.mail_address') }}</span>
                            @endif
                        </div>
                        <div class="form-group">
                            <label>{{ trans('forms.setup.mail_username') }}</label>
                            <input type="text" class="form-control" name="env[mail_username]" value="{{ Binput::old('env.mail_username', $mailConfig['username']) }}" placeholder="{{ trans('forms.setup.mail_username') }}" :required="mail.requiresUsername">
                            @if($errors->has('env.mail_username'))
                            <span class="text-danger">{{ $errors->first('env.mail_username') }}</span>
                            @endif
                        </div>
                        <div class="form-group">
                            <label>{{ trans('forms.setup.mail_password') }}</label>
                            <input type="password" class="form-control" name="env[mail_password]" value="{{ Binput::old('env.mail_password', $mailConfig['password']) }}" autocomplete="off" placeholder="{{ trans('forms.setup.mail_password') }}" :required="mail.requiresUsername">
                            @if($errors->has('env.mail_password'))
                            <span class="text-danger">{{ $errors->first('env.mail_password') }}</span>
                            @endif
                        </div>
                    </fieldset>
                    <hr>
                    <div class="form-group text-center">
                        <span class="wizard-next btn btn-success" data-current-block="1" data-next-block="2" data-loading-text="<i class='icon ion-load-c'></i>">
                            {{ trans('pagination.next') }}
                        </span>
                    </div>
                </div>
                <div class="step block-2 hidden">
                    <fieldset>
                        <div class="form-group">
                            <label>{{ trans('forms.setup.site_name') }}</label>
                            <input type="text" name="settings[app_name]" class="form-control" placeholder="{{ trans('forms.setup.site_name') }}" value="{{ Binput::old('settings.app_name', '') }}" required>
                            @if($errors->has('settings.app_name'))
                            <span class="text-danger">{{ $errors->first('settings.app_name') }}</span>
                            @endif
                        </div>
                        <div class="form-group">
                            <label>{{ trans('forms.setup.site_domain') }}</label>
                            <input type="text" name="settings[app_domain]" class="form-control" placeholder="{{ trans('forms.setup.site_domain') }}" value="{{ Binput::old('settings.app_domain', url('/')) }}" required>
                            @if($errors->has('settings.app_domain'))
                            <span class="text-danger">{{ $errors->first('settings.app_domain') }}</span>
                            @endif
                        </div>
                        <div class="form-group">
                            <label>{{ trans('forms.setup.site_timezone') }}</label>
                            <select name="settings[app_timezone]" class="form-control" required>
                                <option value="">{{ trans('forms.general.timezone') }}</option>
                                @foreach($timezones as $region => $list)
                                <optgroup label="{{ $region }}">
                                @foreach($list as $timezone => $name)
                                <option value="{{ $timezone }}" @if(Binput::old('settings.app_timezone') == $timezone) selected @endif>
                                    {{ $name }}
                                </option>
                                @endforeach
                                </optgroup>
                                @endforeach
                            </select>
                            @if($errors->has('settings.app_timezone'))
                            <span class="text-danger">{{ $errors->first('settings.app_timezone') }}</span>
                            @endif
                        </div>
                        <div class="form-group">
                            <label>{{ trans('forms.setup.site_locale') }}</label>
                            <select name="settings[app_locale]" class="form-control" required>
                                <option value="">Select Language</option>
                                @foreach($langs as $key => $lang)
                                <option value="{{ $key }}" @if(Binput::old('settings.app_locale') == $key || $userLanguage == $key) selected @endif>
                                    {{ $lang['name'] }}
                                </option>
                                @endforeach
                            </select>
                            @if($errors->has('settings.app_locale'))
                            <span class="text-danger">{{ $errors->first('settings.app_locale') }}</span>
                            @endif
                        </div>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="settings[show_support]" value="1" checked>
                                {{ trans("setup.show_support") }}
                            </label>
                        </div>
                        <hr>
                        <div class="form-group text-center">
                            <span class="wizard-next btn btn-info" data-current-block="2" data-next-block="1">
                                {{ trans('pagination.previous') }}
                            </span>
                            <span class="wizard-next btn btn-success" data-current-block="2" data-next-block="3" data-loading-text="<i class='icon ion-load-c'></i>">
                                {{ trans('pagination.next') }}
                            </span>
                        </div>
                    </fieldset>
                </div>
                <div class="step block-3 hidden">
                    <fieldset>
                        <div class="form-group">
                            <label>{{ trans("forms.setup.username") }}</label>
                            <input type="text" name="user[username]" class="form-control" placeholder="{{ trans('forms.setup.username') }}" value="{{ Binput::old('user.username', '') }}" required>
                            @if($errors->has('user.username'))
                            <span class="text-danger">{{ $errors->first('user.username') }}</span>
                            @endif
                        </div>
                        <div class="form-group">
                            <label>{{ trans("forms.setup.email") }}</label>
                            <input type="text" name="user[email]" class="form-control" placeholder="{{ trans('forms.setup.email') }}" value="{{ Binput::old('user.email', '') }}" required>
                            @if($errors->has('user.email'))
                            <span class="text-danger">{{ $errors->first('user.email') }}</span>
                            @endif
                        </div>
                        <div class="form-group">
                            <label>{{ trans("forms.setup.password") }}</label>
                            <input type="password" name="user[password]" class="form-control" placeholder="{{ trans('forms.setup.password') }}" value="{{ Binput::old('user.password', '') }}" required>
                            @if($errors->has('user.password'))
                            <span class="text-danger">{{ $errors->first('user.password') }}</span>
                            @endif
                        </div>
                    </fieldset>
                    <hr >
                    <div class="form-group text-center">
                        <input type="hidden" name="settings[app_incident_days]" value="7" >
                        <input type="hidden" name="settings[app_refresh_rate]" value="0" >
                        <span class="wizard-next btn btn-info" data-current-block="3" data-next-block="2">
                            {{ trans('pagination.previous') }}
                        </span>
                        <span class="wizard-next btn btn-success" data-current-block="3" data-next-block="4" data-loading-text="<i class='icon ion-load-c'></i>">
                            {{ trans("setup.complete_setup") }}
                        </span>
                    </div>
                </div>
                <div class="step block-4 hidden">
                    <div class="setup-success">
                        <i class="ion ion-checkmark-circled"></i>
                        <h3>
                            {{ trans("setup.completed") }}
                        </h3>
                        <a href="{{ cachet_route('dashboard') }}" class="btn btn-default">
                            <span>{{ trans("setup.finish_setup") }}</span>
                        </a>
                    </div>
                </div>
            </form>
        </setup>
    </div>
</div>
@stop
