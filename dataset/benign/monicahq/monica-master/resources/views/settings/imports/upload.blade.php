@extends('layouts.skeleton')

@section('content')

<div class="settings upload">

  {{-- Breadcrumb --}}
  <div class="breadcrumb">
    <div class="{{ Auth::user()->getFluidLayout() }}">
      <div class="row">
        <div class="col-12">
          <ul class="horizontal">
            <li>
                <a href="{{ route('dashboard.index') }}">{{ trans('app.breadcrumb_dashboard') }}</a>
              </li>
              <li>
                <a href="{{ route('settings.index') }}">{{ trans('app.breadcrumb_settings') }}</a>
              </li>
              <li>
                <a href="{{ route('settings.import') }}">{{ trans('app.breadcrumb_settings_import') }}</a>
              </li>
              <li>
                {{ trans('app.breadcrumb_settings_import_upload') }}
              </li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <div class="main-content central-form">
    <div class="{{ auth()->user()->getFluidLayout() }}">
      <div class="row">
        <div class="col-12 col-sm-6 offset-sm-3 offset-sm-3-right">

          <div class="br3 ba b--gray-monica bg-white mb4">
            <div class="pa3 bb b--gray-monica">
              <h2>{{ trans('settings.import_upload_title') }}</h2>

              <div class="warning-zone">
                <p>{{ trans('settings.import_upload_rules_desc') }}</p>
                <ul>
                    <li>{!! trans('settings.import_upload_rule_format') !!}</li>
                    <li>{{ trans('settings.import_upload_rule_vcard') }}</li>
                    <li>{!! trans('settings.import_upload_rule_instructions', [
                      'url1' => 'http://osxdaily.com/2015/07/14/export-contacts-mac-os-x/',
                      'url2' => 'http://www.akruto.com/backup-phone-contacts-calendar/how-to-export-google-contacts-to-csv-or-vcard/'
                    ]) !!}</li>
                    <li>{{ trans('settings.import_upload_rule_multiple') }}</li>
                    <li>{{ trans('settings.import_upload_rule_limit') }}</li>
                    <li>{{ trans('settings.import_upload_rule_time') }}</li>
                    <li>{{ trans('settings.import_upload_rule_cant_revert') }}</li>
                </ul>
              </div>

              @include('partials.errors')

              <form action="{{ route('settings.storeImport') }}" method="POST" enctype="multipart/form-data">
                @csrf

                  <div class="form-group">
                      <label for="vcard">{!! trans('settings.import_upload_form_file') !!}</label>
                      <input type="file" class="form-control-file" name="vcard" id="vcard">
                      <small id="fileHelp" class="form-text text-muted">{{ trans('people.information_edit_max_size', ['size' => config('monica.max_upload_size')]) }}</small>
                  </div>

                <div class="form-group">
                    <label for="behaviour">{{ trans('settings.import_upload_behaviour') }}</label>
                    <select class="form-control" name="behaviour" id="behaviour">
                        <option value="{{ \App\Services\VCard\ImportVCard::BEHAVIOUR_ADD }}" selected>{{ trans('settings.import_upload_behaviour_add') }}</option>
                        <option value="{{ \App\Services\VCard\ImportVCard::BEHAVIOUR_REPLACE }}">{{ trans('settings.import_upload_behaviour_replace') }}</option>
                    </select>
                    <small id="behaviourHelp" class="form-text text-muted">{{ trans('settings.import_upload_behaviour_help') }}</small>
                </div>

                <div class="form-group actions">
                    <button id="upload" type="submit" class="btn btn-primary">{{ trans('app.upload') }}</button>
                    <a href="{{ route('settings.import') }}" class="btn btn-secondary">{{ trans('app.cancel') }}</a>
                </div> <!-- .form-group -->
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection
