@extends('header')

@section('content')

	{!! Former::open($url)
            ->addClass('col-lg-10 col-lg-offset-1 warn-on-exit')
            ->method($method)
            ->rules([
                'name' => 'required',
            ]) !!}

    @if ($category)
        {!! Former::populate($category) !!}
    @endif

    <span style="display:none">
        {!! Former::text('public_id') !!}
    </span>

	<div class="row">
        <div class="col-lg-10 col-lg-offset-1">

            <div class="panel panel-default">
            <div class="panel-body">

                {!! Former::text('name') !!}

            </div>
            </div>

        </div>
    </div>


	<center class="buttons">
        {!! Button::normal(trans('texts.cancel'))->large()->asLinkTo(HTMLUtils::previousUrl('/expense_categories'))->appendIcon(Icon::create('remove-circle')) !!}
        {!! Button::success(trans('texts.save'))->submit()->large()->appendIcon(Icon::create('floppy-disk')) !!}
		@if ($category && Auth::user()->can('create', ENTITY_EXPENSE))
	    	{!! Button::primary(trans('texts.new_expense'))->large()
					->asLinkTo(url("/expenses/create/0/0/{$category->public_id}"))
					->appendIcon(Icon::create('plus-sign')) !!}
		@endif
	</center>

	{!! Former::close() !!}

    <script>
        $(function() {
            $('#name').focus();
        });
    </script>

@stop
