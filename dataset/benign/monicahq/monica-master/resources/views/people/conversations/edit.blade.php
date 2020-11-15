@extends('layouts.skeleton')

@section('content')

<section class="ph3 ph0-ns">

  {{-- Breadcrumb --}}
  <div class="mt4 mw7 center mb3">
    <p><a href="{{ route('people.show', $contact) }}">&lt; {{ $contact->name }}</a></p>
    <div class="mt4 mw7 center mb3">
      <h3 class="f3 fw5">{{ trans('people.conversation_edit_title') }}</h3>
      <form method="POST" action="{{ route('people.conversations.destroy', [$contact, $conversation]) }}">
        @method('DELETE')
        @csrf
        <confirm message="{{ trans('people.conversation_edit_delete') }}" link-class="w-auto-ns w-100 mb2 pb0-ns">
          {{ trans('people.conversation_delete_link') }}
        </confirm>
      </form>
    </div>
  </div>

  <div class="mw7 center br3 ba b--gray-monica bg-white mb6">

    @if (session('status'))
    <div class="alert alert-success">
        {{ session('status') }}
    </div>
    @endif

    @include('partials.errors')

    <form action="{{ route('people.conversations.update', [$contact, $conversation]) }}" method="POST" enctype="multipart/form-data">
      @method('PUT')
      @csrf

      {{-- When did it take place --}}
      <div class="pa4-ns ph3 pv2 mb3 mb0-ns bb b--gray-monica">
        <p class="mb2 b">{{ trans('people.conversation_add_when') }}</p>
        <div class="">
          <div class="di {{ htmldir() == 'ltr' ? 'mr3' : 'ml3' }}">
            <input type="radio" class="mr1" id="today" name="conversationDateRadio" value="today">
            <label for="today" class="pointer">{{ trans('app.today') }}</label>
          </div>
          <div class="di {{ htmldir() == 'ltr' ? 'mr3' : 'ml3' }}">
            <input type="radio" class="mr1" id="yesterday" name="conversationDateRadio" value="yesterday">
            <label for="yesterday" class="pointer">{{ trans('app.yesterday') }}</label>
          </div>
          <div class="di {{ htmldir() == 'ltr' ? 'mr3' : 'ml3' }}">
            <input type="radio" id="another" name="conversationDateRadio" value="another" checked>
            <label for="another" class="pointer mr2">{{ trans('app.another_day') }}
            <span class="dib">
              <form-date
                :id="'conversationDate'"
                :default-date="'{{ now() }}'"
                :value="'{{ $conversation->happened_at }}'"
                :locale="'{{ \App::getLocale() }}'">
              </form-date>
            </span></label>
          </div>
        </div>
      </div>

      {{-- What tool did you use --}}
      <div class="pa4-ns ph3 pv2 mb3 mb0-ns bb b--gray-monica">
        <form-select
          :options="{{ $contactFieldTypes }}"
          :required="true"
          value="{{ $conversation->contact_field_type_id }}"
          :title="'{{ trans('people.conversation_add_how') }}'"
          :id="'contactFieldTypeId'">
        </form-select>
      </div>

      {{-- Conversation --}}
      <conversation participant-name="{{ $contact->first_name }}" :existing-messages="{{ $messages }}"></conversation>

      {{-- Form actions --}}
      <div class="ph4-ns ph3 pv3 bb b--gray-monica">
        <div class="flex-ns justify-between">
          <div class="">
            <a href="{{ route('people.show', $contact) }}" class="btn btn-secondary tc w-auto-ns w-100 mb2 pb0-ns">{{ trans('app.cancel') }}</a>
          </div>
          <div class="">
            <button class="btn btn-primary w-auto-ns w-100 mb2 pb0-ns" name="save" type="submit">{{ trans('app.update') }}</button>
          </div>
        </div>
      </div>

    </form>
  </div>
</section>

@endsection
