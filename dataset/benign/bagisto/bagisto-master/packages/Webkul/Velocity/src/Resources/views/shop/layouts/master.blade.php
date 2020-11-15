@php
    $velocityHelper = app('Webkul\Velocity\Helpers\Helper');
    $velocityMetaData = $velocityHelper->getVelocityMetaData();
    
    view()->share('velocityMetaData', $velocityMetaData);
@endphp

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

    <head>
        <title>@yield('page_title')</title>

        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta http-equiv="content-language" content="{{ app()->getLocale() }}">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

        <link rel="stylesheet" href="{{ asset('themes/velocity/assets/css/velocity.css') }}" />
        <link rel="stylesheet" href="{{ asset('themes/velocity/assets/css/bootstrap.min.css') }}" />
        <link rel="stylesheet" href="{{ asset('themes/velocity/assets/css/google-font.css') }}" />

        @if (core()->getCurrentLocale()->direction == 'rtl')
            <link href="{{ asset('themes/velocity/assets/css/bootstrap-flipped.css') }}" rel="stylesheet">
        @endif

        @if ($favicon = core()->getCurrentChannel()->favicon_url)
            <link rel="icon" sizes="16x16" href="{{ $favicon }}" />
        @else
            <link rel="icon" sizes="16x16" href="{{ asset('/themes/velocity/assets/images/static/v-icon.png') }}" />
        @endif

        <script
            type="text/javascript"
            src="{{ asset('themes/velocity/assets/js/jquery.min.js') }}">
        </script>

        <script
            type="text/javascript"
            baseUrl="{{ url()->to('/') }}"
            src="{{ asset('themes/velocity/assets/js/velocity.js') }}">
        </script>

        <script
            type="text/javascript"
            src="{{ asset('themes/velocity/assets/js/jquery.ez-plus.js') }}">
        </script>

        @yield('head')

        @section('seo')
            <meta name="description" content="{{ core()->getCurrentChannel()->description }}"/>
        @show

        @stack('css')

        {!! view_render_event('bagisto.shop.layout.head') !!}

    </head>

    <body @if (core()->getCurrentLocale()->direction == 'rtl') class="rtl" @endif>
        {!! view_render_event('bagisto.shop.layout.body.before') !!}

        @include('shop::UI.particals')

        <div id="app">
            {{-- <responsive-sidebar v-html="responsiveSidebarTemplate"></responsive-sidebar> --}}

            <product-quick-view v-if="$root.quickView"></product-quick-view>

            <div class="main-container-wrapper">

                @section('body-header')
                    @include('shop::layouts.top-nav.index')

                    {!! view_render_event('bagisto.shop.layout.header.before') !!}

                        @include('shop::layouts.header.index')

                    {!! view_render_event('bagisto.shop.layout.header.after') !!}

                    <div class="main-content-wrapper col-12 no-padding">
                        @php
                            $velocityContent = app('Webkul\Velocity\Repositories\ContentRepository')->getAllContents();
                        @endphp

                        <content-header
                            url="{{ url()->to('/') }}"
                            :header-content="{{ json_encode($velocityContent) }}"
                            heading= "{{ __('velocity::app.menu-navbar.text-category') }}"
                            category-count="{{ $velocityMetaData ? $velocityMetaData->sidebar_category_count : 10 }}"
                        ></content-header>

                        <div class="">
                            <div class="row col-12 remove-padding-margin">
                                <sidebar-component
                                    main-sidebar=true
                                    id="sidebar-level-0"
                                    url="{{ url()->to('/') }}"
                                    category-count="{{ $velocityMetaData ? $velocityMetaData->sidebar_category_count : 10 }}"
                                    add-class="category-list-container pt10">
                                </sidebar-component>

                                <div
                                    class="col-12 no-padding content" id="home-right-bar-container">

                                    <div class="container-right row no-margin col-12 no-padding">

                                        {!! view_render_event('bagisto.shop.layout.content.before') !!}

                                        @yield('content-wrapper')

                                        {!! view_render_event('bagisto.shop.layout.content.after') !!}
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                @show

                <div class="container">

                    {!! view_render_event('bagisto.shop.layout.full-content.before') !!}

                        @yield('full-content-wrapper')

                    {!! view_render_event('bagisto.shop.layout.full-content.after') !!}

                </div>
            </div>

            <div class="modal-parent" id="loader" style="top: 0" v-show="showPageLoader">
                <overlay-loader :is-open="true"></overlay-loader>
            </div>
        </div>

        <!-- below footer -->
        @section('footer')
            {!! view_render_event('bagisto.shop.layout.footer.before') !!}

                @include('shop::layouts.footer.index')

            {!! view_render_event('bagisto.shop.layout.footer.after') !!}
        @show

        {!! view_render_event('bagisto.shop.layout.body.after') !!}

        <div id="alert-container"></div>

        <script type="text/javascript">
            (() => {
                window.showAlert = (messageType, messageLabel, message) => {
                    if (messageType && message !== '') {
                        let alertId = Math.floor(Math.random() * 1000);

                        let html = `<div class="alert ${messageType} alert-dismissible" id="${alertId}">
                            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                            <strong>${messageLabel ? messageLabel + '!' : ''} </strong> ${message}.
                        </div>`;

                        $('#alert-container').append(html).ready(() => {
                            window.setTimeout(() => {
                                $(`#alert-container #${alertId}`).remove();
                            }, 5000);
                        });
                    }
                }

                let messageType = '';
                let messageLabel = '';

                @if ($message = session('success'))
                    messageType = 'alert-success';
                    messageLabel = "{{ __('velocity::app.shop.general.alert.success') }}";
                @elseif ($message = session('warning'))
                    messageType = 'alert-warning';
                    messageLabel = "{{ __('velocity::app.shop.general.alert.warning') }}";
                @elseif ($message = session('error'))
                    messageType = 'alert-danger';
                    messageLabel = "{{ __('velocity::app.shop.general.alert.error') }}";
                @elseif ($message = session('info'))
                    messageType = 'alert-info';
                    messageLabel = "{{ __('velocity::app.shop.general.alert.info') }}";
                @endif

                if (messageType && '{{ $message }}' !== '') {
                    window.showAlert(messageType, messageLabel, '{{ $message }}');
                }

                window.serverErrors = [];
                @if (isset($errors))
                    @if (count($errors))
                        window.serverErrors = @json($errors->getMessages());
                    @endif
                @endif

                window._translations = @json(app('Webkul\Velocity\Helpers\Helper')->jsonTranslations());
            })();
        </script>

        <script
            type="text/javascript"
            src="{{ asset('vendor/webkul/ui/assets/js/ui.js') }}">
        </script>

        @stack('scripts')
    </body>
</html>
