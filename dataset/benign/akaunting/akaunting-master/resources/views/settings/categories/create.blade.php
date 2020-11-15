@extends('layouts.admin')

@section('title', trans('general.title.new', ['type' => trans_choice('general.categories', 1)]))

@section('content')
    <div class="card">
        {!! Form::open([
            'route' => 'categories.store',
            'id' => 'category',
            '@submit.prevent' => 'onSubmit',
            '@keydown' => 'form.errors.clear($event.target.name)',
            'files' => true,
            'role' => 'form',
            'class' => 'form-loading-button',
            'novalidate' => true
        ]) !!}

            <div class="card-body">
                <div class="row">
                    {{ Form::textGroup('name', trans('general.name'), 'font') }}

                    {{ Form::selectGroup('type', trans_choice('general.types', 1), 'bars', $types, config('general.types')) }}

                    @stack('color_input_start')
                        <div class="form-group col-md-6 required {{ $errors->has('color') ? 'has-error' : ''}}">
                            {!! Form::label('color', trans('general.color'), ['class' => 'form-control-label']) !!}
                            <div class="input-group input-group-merge" id="category-color-picker">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <el-color-picker v-model="color" size="mini" :predefine="predefineColors" @change="onChangeColor"></el-color-picker>
                                    </span>
                                </div>
                                {!! Form::text('color', '#55588b', ['v-model' => 'form.color', '@input' => 'onChangeColorInput', 'id' => 'color', 'class' => 'form-control color-hex', 'required' => 'required']) !!}
                            </div>
                            {!! $errors->first('color', '<p class="help-block">:message</p>') !!}
                        </div>
                    @stack('color_input_end')

                    {{ Form::radioGroup('enabled', trans('general.enabled'), true) }}
                </div>
            </div>

            <div class="card-footer">
                <div class="row save-buttons">
                    {{ Form::saveButtons('categories.index') }}
                </div>
            </div>
        {!! Form::close() !!}
    </div>
@endsection

@push('scripts_start')
    <script src="{{ asset('public/js/settings/categories.js?v=' . version('short')) }}"></script>
@endpush
