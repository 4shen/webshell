@extends('marketing.skeleton')

@section('content')
  <body class="marketing register">
    <div class="container">
      <div class="row">
        <div class="col-12 col-md-6 offset-md-3 offset-md-3-right">

          <div class="signup-box">
            <div class="dt w-100">
              <div class="dtc tc">
                <img src="img/monica.svg" width="97" height="88" alt="">
              </div>
            </div>
            <h2>{{ trans('settings.users_accept_title') }}</h2>

            @include ('partials.errors')

            <form action="{{ route('invitations.send', $key) }}" method="post">
              @csrf

              <div class="form-group">
                <label for="email">{{ trans('auth.register_email') }}</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="{{ trans('auth.register_email_example') }}" value="{{ $email ?? old('email') }}" required autocomplete="email" autofocus>
              </div>

              <div class="row">
                <div class="col-12 col-sm-6">
                  <div class="form-group">
                    <label for="first_name">{{ trans('auth.register_firstname') }}</label>
                    <input type="text" class="form-control" id="first_name" name="first_name" placeholder="{{ trans('auth.register_firstname_example') }}" value="{{ old('first_name') }}" required autocomplete="given-name">
                  </div>
                </div>
                <div class="col-12 col-sm-6">
                  <div class="form-group">
                    <label for="last_name">{{ trans('auth.register_lastname') }}</label>
                    <input type="text" class="form-control" id="last_name" name="last_name" placeholder="{{ trans('auth.register_lastname_example') }}" value="{{ old('last_name') }}" required autocomplete="family-name">
                  </div>
                </div>
              </div>

              <div class="form-group">
                <label for="password">{{ trans('auth.register_password') }}</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="{{ trans('auth.register_password_example') }}" required autocomplete="password">
              </div>

              <div class="form-group">
                <label for="password_confirmation">{{ trans('auth.register_password_confirmation') }}</label>
                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required autocomplete="password">
              </div>

              <div class="form-group">
                <label for="email_security">{{ trans('auth.register_invitation_email') }}</label>
                <input type="email" class="form-control" id="email_security" name="email_security" required>
              </div>

              <!-- Policy acceptance check -->
              <div class="form-check">
                <label class="form-check-label">
                  <input class="form-check-input" id="policy" name="policy" type="checkbox" value="policy" required>
                  {!! trans('auth.register_policy', ['url' => 'https://monicahq.com/privacy', 'urlterm' => 'https://monicahq.com/terms', 'hreflang' => 'en', ]) !!}
                </label>
              </div>

              <div class="form-group actions">
                <button type="submit" class="btn btn-primary">{{ trans('auth.register_action') }}</button>
              </div>

            </form>
          </div>
        </div>
      </div>
    </div>
  </body>
@endsection
