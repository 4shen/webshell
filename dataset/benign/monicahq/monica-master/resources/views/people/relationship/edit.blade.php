@extends('layouts.skeleton')

@section('content')

<section class="ph3 ph0-ns">

  {{-- Breadcrumb --}}
  <div class="mt4 mw7 center mb3">
    <p><a href="{{ route('people.show', $contact) }}">&lt; {{ $contact->name }}</a></p>
    <div class="mt4 mw7 center mb3">
      <h3 class="f3 fw5">{{ trans('people.relationship_form_edit') }}</h3>
    </div>
  </div>

  <div class="mw7 center br3 ba b--gray-monica bg-white mb6">

    @if (session('status'))
    <div class="alert alert-success">
        {{ session('status') }}
    </div>
    @endif

    @include('partials.errors')

    <form action="{{ route('people.relationships.update', [$contact, $relationshipId]) }}" method="POST">
      @method('PUT')
      @csrf
      <input type="hidden" name="type" value="{{ $type }}">

      @if ($partner->is_partial)
        {{-- Name --}}
        <div class="pa4-ns ph3 pv2 bb b--gray-monica">
          {{-- This check is for the cultures that are used to say the last name first --}}
          <div class="mb3 mb0-ns">
            @if ($formNameOrder == 'firstname')

            <div class="dt dt--fixed">
              <div class="dtc pr2">
                <form-input
                  value="{{ $partner->first_name }}"
                  :input-type="'text'"
                  :id="'first_name'"
                  :required="true"
                  :title="'{{ trans('people.people_add_firstname') }}'">
                </form-input>
              </div>
              <div class="dtc">
                <form-input
                  value="{{ $partner->last_name }}"
                  :input-type="'text'"
                  :id="'last_name'"
                  :required="false"
                  :title="'{{ trans('people.people_add_lastname') }}'">
                </form-input>
              </div>
            </div>

            @else

            <div class="dt dt--fixed">
              <div class="dtc pr2">
                <form-input
                  value="{{ $partner->last_name }}"
                  :input-type="'text'"
                  :id="'last_name'"
                  :required="false"
                  :title="'{{ trans('people.people_add_lastname') }}'">
                </form-input>
              </div>
              <div class="dtc">
                <form-input
                  value="{{ $partner->first_name }}"
                  :input-type="'text'"
                  :id="'first_name'"
                  :required="true"
                  :title="'{{ trans('people.people_add_firstname') }}'">
                </form-input>
              </div>
            </div>

            @endif
          </div>
        </div>

        {{-- Gender --}}
        <div class="pa4-ns ph3 pv2 mb3 mb0-ns bb b--gray-monica">
          <form-select
            :options="{{ $genders }}"
            :required="false"
            value="{{ $partner->gender_id }}"
            :title="'{{ trans('people.people_add_gender') }}'"
            :id="'gender_id'">
          </form-select>
        </div>

        {{-- Birthdate --}}
        <form-specialdate
          :months="{{ $months }}"
          :days="{{ $days }}"
          :month="{{ $month }}"
          :day="{{ $day }}"
          :age="{{ $age ?: 0 }}"
          :birthdate="'{{ $birthdate }}'"
          :reminder="{{ \Safe\json_encode($hasBirthdayReminder) }}"
          :value="'{{ $birthdayState }}'"
        ></form-specialdate>

        <div class="pa4-ns ph3 pv2 bb b--gray-monica">
          {{-- Real or partial contact (false in this case) --}}
          <form-checkbox
            :name="'realContact'"
            :iclass="'pa0 ma0 lh-copy'"
            :dclass="'mb3 mb0-ns flex'"
            value="1"
            :model-value="false"
          >
            <template slot="label">
              {{ trans('people.relationship_form_also_create_contact') }}
            </template>
            <span slot="extra" class="silver">
              {{ trans('people.relationship_form_add_description') }}
            </span>
          </form-checkbox>
        </div>
      @endif

      {{-- Nature of relationship --}}
      <div class="pa4-ns ph3 pv2 mb3 mb0-ns bb b--gray-monica">
        <form-select
          :options="{{ $relationshipTypes }}"
          value="{{ $type }}"
          :required="true"
          :title="'{{
            $partner->is_partial ?
              trans('people.relationship_form_is_with') :
              trans('people.relationship_form_is_with_name', ['name' => $partner->name])
            }}'"
          :id="'relationship_type_id'">
        </form-select>
      </div>

      {{-- Form actions --}}
      <div class="ph4-ns ph3 pv3 bb b--gray-monica">
        <div class="flex-ns justify-between">
          <div>
            <a href="{{ route ('people.show', $contact) }}" class="btn btn-secondary w-auto-ns w-100 mb2 pb0-ns">{{ trans('app.cancel') }}</a>
          </div>
          <div>
            <button class="btn btn-primary w-auto-ns w-100 mb2 pb0-ns" v-if="global_relationship_form_new_contact" name="save" type="submit">{{ trans('app.save') }}</button>
          </div>
        </div>
      </div>

    </form>
  </div>
</section>

@endsection
