{{-- Version check --}}

@if (config('monica.check_version'))

    @if (version_compare($instance->latest_version, config('monica.app_version')) > 0)
    <li>
        <a href="#showVersion" data-toggle="modal" class="badge badge-success">{{ trans('app.footer_new_version') }}</a>
    </li>
    @endif

    <!-- Modal -->
    <div class="modal show-version fade" id="showVersion" tabindex="-1">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ trans('app.footer_modal_version_whats_new') }}</h5>
            <button type="button" class="close" data-dismiss="modal">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
          <p>{{ trans_choice('app.footer_modal_version_release_away', $instance->number_of_versions_since_current_version, ['number' => $instance->number_of_versions_since_current_version]) }}</p>
          {!! $instance->latest_release_notes !!}
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ trans('app.close') }}</button>
          </div>
        </div>
      </div>
    </div>

@endif
