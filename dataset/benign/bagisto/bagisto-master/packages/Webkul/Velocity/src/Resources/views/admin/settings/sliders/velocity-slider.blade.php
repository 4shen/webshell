<div class="control-group">
    <label>{{ __('velocity::app.admin.meta-data.slider-path') }}</label>

    <input
        type="text"
        class="control"
        name="slider_path"
        value="{{ (isset($slider) && $slider->slider_path) ? $slider->slider_path : '' }}" />
</div>