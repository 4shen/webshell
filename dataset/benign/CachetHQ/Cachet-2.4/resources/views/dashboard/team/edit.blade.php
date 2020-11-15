@extends('layout.dashboard')

@section('content')
<div class="header">
    <div class="sidebar-toggler visible-xs">
        <i class="ion ion-navicon"></i>
    </div>
    <span class="uppercase">
        <i class="ion ion-ios-people-outline"></i> {{ trans('dashboard.team.member') }}
    </span>
</div>
<div class="content-wrapper">
    <div class="row">
        <div class="col-sm-12">
            @include('partials.errors')
            <form name="UserForm" class="form-vertical" role="form" action="/dashboard/team/{{ $user->id }}" method="POST">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <fieldset>
                    <div class="form-group">
                        <label>{{ trans('forms.user.username') }}</label>
                        <input type="text" class="form-control" name="username" value="{{ $user->username }}" required placeholder="{{ trans('forms.user.username') }}">
                    </div>
                    <div class="form-group">
                        <label>{{ trans('forms.user.email') }}</label>
                        <input type="email" class="form-control" name="email" value="{{ $user->email }}" required placeholder="{{ trans('forms.user.email') }}">
                    </div>
                    <div class="form-group">
                        <label>{{ trans('forms.user.password') }}</label>
                        <input type="password" class="form-control" name="password" value="" {{ !$currentUser->isAdmin ? "disabled": "" }} placeholder="{{ trans('forms.user.password') }}">
                    </div>
                    @if($currentUser->isAdmin)
                    <div class="form-group">
                        <label>{{ trans('forms.user.user_level') }}</label>
                        <select name="level" class="form-control">
                            <option value="2" {{ $user->level === 2 ? "selected" : null }}>{{ trans('forms.user.levels.user') }}</option>
                            <option value="1" {{ $user->level === 1 ? "selected" : null }}>{{ trans('forms.user.levels.admin') }}</option>
                        </select>
                    </div>
                    @endif
                </fieldset>

                <div class="form-group">
                    <button type="submit" class="btn btn-success">{{ trans('forms.update') }}</button>
                    @if($currentUser->isAdmin)
                    <a class="btn btn-info" href="{{ cachet_route('dashboard.user.api.regen', [$user->id]) }}">{{ trans('cachet.api.revoke') }}</a>
                    @if($currentUser->id != $user->id)
                    <a class="btn btn-danger confirm-action" href="{{ cachet_route('dashboard.team.delete', [$user->id], 'delete') }}" data-method="DELETE">{{ trans('forms.delete') }}</a>
                    @endif
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>
@stop
