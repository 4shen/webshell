<div class="sidebar-box">

  @if (is_null($contact->food_preferences))

    <p class="sidebar-box-title">
      <strong>{{ trans('people.food_preferences_title') }}</strong>
    </p>

    <p class="sidebar-box-paragraph">
      <a href="{{ route('people.food.index', $contact) }}">{{ trans('app.add') }}</a>
    </p>

  @else

    <p class="sidebar-box-title">
      <strong>{{ trans('people.food_preferences_title') }}</strong>
    </p>

    {{-- Information about the significant other --}}
    <p class="sidebar-box-paragraph">
      {{ $contact->food_preferences }}
      <a href="{{ route('people.food.index', $contact) }}" class="action-link">{{ trans('app.edit') }}</a>
    </p>

  @endif

</div>
