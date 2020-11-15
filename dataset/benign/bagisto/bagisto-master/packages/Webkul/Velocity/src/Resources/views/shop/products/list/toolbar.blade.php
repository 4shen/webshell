@inject ('toolbarHelper', 'Webkul\Product\Helpers\Toolbar')

{!! view_render_event('bagisto.shop.products.list.toolbar.before') !!}
    <toolbar-component></toolbar-component>
{!! view_render_event('bagisto.shop.products.list.toolbar.after') !!}

@push('scripts')
    <script type="text/x-template" id="toolbar-template">
        <div class="toolbar-wrapper" v-if='!isMobile()'>
            <div class="view-mode">
                @if (
                    ! ($toolbarHelper->isModeActive('grid')
                    || $toolbarHelper->isModeActive('list'))
                )
                    <div class="rango-view-grid-container active">
                        <span class="rango-view-grid fs24"></span>
                    </div>
                @else
                    @if ($toolbarHelper->isModeActive('grid'))
                        <div class="rango-view-grid-container active">
                            <span class="rango-view-grid fs24"></span>
                        </div>
                    @else
                        <div class="rango-view-grid-container">
                            <a href="{{ $toolbarHelper->getModeUrl('grid') }}" class="grid-view unset">
                                <span class="rango-view-grid fs24"></span>
                            </a>
                        </div>
                    @endif
                @endif

                @if ($toolbarHelper->isModeActive('list'))
                    <div class="rango-view-list-container active">
                        <span class="rango-view-list fs24"></span>
                    </div>
                @else
                    <div class="rango-view-list-container">
                        <a
                            href="{{ $toolbarHelper->getModeUrl('list') }}"
                            class="list-view unset">
                            <span class="rango-view-list fs24"></span>
                        </a>
                    </div>
                @endif
            </div>

            <div class="sorter">
                <label>{{ __('shop::app.products.sort-by') }}</label>

                <select class="selective-div border-normal styled-select" onchange="window.location.href = this.value">
                    @foreach ($toolbarHelper->getAvailableOrders() as $key => $order)
                        <option value="{{ $toolbarHelper->getOrderUrl($key) }}" {{ $toolbarHelper->isOrderCurrent($key) ? 'selected' : '' }}>
                            {{ __('shop::app.products.' . $order) }}
                        </option>
                    @endforeach
                </select>

                <div class="select-icon-container">
                    <span class="select-icon rango-arrow-down"></span>
                </div>
            </div>

            <div class="limiter">
                <label>{{ __('shop::app.products.show') }}</label>

                <select class="selective-div border-normal styled-select" onchange="window.location.href = this.value" style="width: 57px;">

                    @foreach ($toolbarHelper->getAvailableLimits() as $limit)

                        <option value="{{ $toolbarHelper->getLimitUrl($limit) }}" {{ $toolbarHelper->isLimitCurrent($limit) ? 'selected' : '' }}>
                            {{ $limit }}
                        </option>

                    @endforeach

                </select>

                <div class="select-icon-container">
                    <span class="select-icon rango-arrow-down"></span>
                </div>
            </div>
        </div>

        <div class="toolbar-wrapper row col-12 remove-padding-margin" v-else>
            <div
                v-if="layeredNavigation"
                class="nav-container scrollable"
                style="
                    z-index: 1000;
                    color: black;
                    position: relative;
                ">
                <div class="header drawer-section">
                    <i class="material-icons" @click="toggleLayeredNavigation">keyboard_backspace</i>

                    <span class="fs24 fw6">
                        {{ __('velocity::app.shop.general.filter') }}
                    </span>
                    <span class="pull-right link-color" @click="toggleLayeredNavigation">
                        {{ __('velocity::app.responsive.header.done') }}
                    </span>
                </div>

                @if (request()->route()->getName() != 'velocity.search.index')
                    @include ('shop::products.list.layered-navigation')
                @endif
            </div>

            <div class="col-4" @click="toggleLayeredNavigation({event: $event, actionType: 'open'})">
                <a class="unset">
                    <i class="material-icons">filter_list</i>
                    <span>{{ __('velocity::app.shop.general.filter') }}</span>
                </a>
            </div>

            <div class="col-4">
                <a
                    class="unset"
                    href="{{
                        $toolbarHelper->isOrderCurrent('name-asc')
                        ? $toolbarHelper->getOrderUrl('name-asc')
                        : $toolbarHelper->getOrderUrl('name-desc')
                    }}">

                    <i class="material-icons">sort_by_alpha</i>
                    <span>{{ __('shop::app.products.sort-by') }}</span>
                </a>
            </div>

            <div class="col-4">
                @php
                    $isList = $toolbarHelper->isModeActive('list');
                @endphp

                <a
                    class="unset"
                    href="{{
                        $isList
                        ? $toolbarHelper->getModeUrl('grid')
                        : $toolbarHelper->getModeUrl('list')
                    }}">

                    <i class="material-icons">
                        @if ($isList) list @else view_module @endif
                    </i>
                    <span>{{ __('velocity::app.shop.general.view') }}</span>
                </a>
            </div>
        </div>
    </script>

    <script type="text/javascript">
        (() => {
            Vue.component('toolbar-component', {
                template: '#toolbar-template',
                data: function () {
                    return {
                        'layeredNavigation': false,
                    }
                },

                watch: {
                    layeredNavigation: function (value) {
                        if (value) {
                            document.body.classList.add('open-hamburger');
                        } else {
                            document.body.classList.remove('open-hamburger');
                        }
                    }
                },

                methods: {
                    toggleLayeredNavigation: function ({event, actionType}) {
                        this.layeredNavigation = !this.layeredNavigation;
                    }
                }
            })
        })()
    </script>
@endpush
