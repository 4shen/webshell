@extends('layout.dashboard')

@section('content')
<div class="header fixed">
    <div class="sidebar-toggler visible-xs">
        <i class="ion ion-navicon"></i>
    </div>
    <span class="uppercase">
        <i class="ion ion-ios-people-outline"></i> {{ trans('dashboard.team.team') }}
    </span>
    @if($currentUser->isAdmin)
    <div class="button-group pull-right">
        <a class="btn btn-sm btn-success" href="{{ cachet_route('dashboard.team.invite') }}">
            {{ trans('dashboard.team.invite.title') }}
        </a>
        <a class="btn btn-sm btn-success" href="{{ cachet_route('dashboard.team.create') }}">
            {{ trans('dashboard.team.add.title') }}
        </a>
    </div>
    @endif
    <div class="clearfix"></div>
</div>
<div class="content-wrapper header-fixed">
    <div class="row">
        <div class="col-sm-12">
            <p class="lead">{{ trans('dashboard.team.description') }}</p>

            <div class="user-grid">
                @foreach($teamMembers as $member)
                <a href="@if($currentUser->id == $member->id) {{ cachet_route('dashboard.team.edit', $member) }} @else /dashboard/team/{{ $member->id }} @endif">
                    <div class="user col-sm-3 col-xs-6">
                        <div class="name">{{ $member->username }}</div>
                        <div class="email">{{ $member->email }}</div>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
    </div>
</div>
@stop
