@extends('marketing.auth')

@section('content')
    <div class="container">
      <form action="{{ session('oauth') ? route('oauth.validate2fa') : route('validate2fa') }}" method="post">
        <input type="hidden" name="url" value="{{ urlencode(url()->current()) }}" />
        <div class="row">
          <div class="col-12 col-md-6 offset-md-3 offset-md-3-right">
            <div class="signup-box">

              <div class="dt w-100">
                <div class="dtc tc">
                  <img src="img/monica.svg" width="97" height="88" alt="">
                </div>
              </div>
              <h2>{{ trans('auth.2fa_title') }}</h2>

              @include ('partials.errors')

              @csrf

              <h3>{{ trans('auth.mfa_auth_otp') }}</h3>
              @include ('partials.auth.validate2fa')

              <div class="form-group links">
                <ul>
                  <li>{!! trans('auth.use_recovery', ['url' => route('recovery.login')]) !!}</li>
                </ul>
              </div>
            </div>
          </div>
        </form>
    </div>
@endsection
