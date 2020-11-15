@php
    $attributeRepository = app('\Webkul\Attribute\Repositories\AttributeRepository');
    $comparableAttributes = $attributeRepository->findByField('is_comparable', 1);
@endphp

@push('css')
    <style>
        .btn-add-to-cart {
            max-width: 130px;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }
    </style>
@endpush

@push('scripts')
    <script type="text/x-template" id="compare-product-template">
        <section class="cart-details row no-margin col-12">
            <h1 class="fw6 col-6">
                {{ __('velocity::app.customer.compare.compare_similar_items') }}
            </h1>

            <div class="col-6" v-if="products.length > 0">
                <button
                    class="theme-btn light pull-right"
                    @click="removeProductCompare('all')">
                    {{ __('shop::app.customer.account.wishlist.deleteall') }}
                </button>
            </div>

            {!! view_render_event('bagisto.shop.customers.account.compare.view.before') !!}

            <table class="row compare-products">
                <shimmer-component v-if="!isProductListLoaded && !isMobile()"></shimmer-component>

                <template v-else-if="isProductListLoaded && products.length > 0">
                    @php
                        $comparableAttributes = $comparableAttributes->toArray();

                        array_splice($comparableAttributes, 1, 0, [[
                            'code' => 'image',
                            'admin_name' => __('velocity::app.customer.compare.product_image')
                        ]]);

                        array_splice($comparableAttributes, 2, 0, [[
                            'code' => 'addToCartHtml',
                            'admin_name' => __('velocity::app.customer.compare.actions')
                        ]]);
                    @endphp

                    @foreach ($comparableAttributes as $attribute)
                        <tr>
                            <td>
                                <span class="fs16">{{ isset($attribute['name']) ? $attribute['name'] : $attribute['admin_name'] }}</span>
                            </td>

                            <td :key="`title-${index}`" v-for="(product, index) in products">
                                @switch ($attribute['code'])
                                    @case('name')
                                        <a :href="`${$root.baseUrl}/${product.url_key}`" class="unset remove-decoration active-hover">
                                            <h1 class="fw6 fs18" v-text="product['{{ $attribute['code'] }}']"></h1>
                                        </a>
                                        @break

                                    @case('image')
                                        <a :href="`${$root.baseUrl}/${product.url_key}`" class="unset">
                                            <img
                                                class="image-wrapper"
                                                :src="product['{{ $attribute['code'] }}']"
                                                onload="window.updateHeight ? window.updateHeight() : ''"
                                                :onerror="`this.src='${$root.baseUrl}/vendor/webkul/ui/assets/images/product/large-product-placeholder.png'`" />
                                        </a>
                                        @break

                                    @case('price')
                                        <span v-html="product['priceHTML']"></span>
                                        @break

                                    @case('addToCartHtml')
                                        <div class="action">
                                            <vnode-injector :nodes="getDynamicHTML(product.addToCartHtml)"></vnode-injector>

                                            <i
                                                class="material-icons cross fs16"
                                                @click="removeProductCompare(product.id)">

                                                close
                                            </i>
                                        </div>
                                        @break

                                    @case('color')
                                        <span v-html="product.color_label" class="fs16"></span>
                                        @break

                                    @case('size')
                                        <span v-html="product.size_label" class="fs16"></span>
                                        @break

                                    @case('description')
                                        <span v-html="product.description"></span>
                                        @break

                                    @default
                                        @switch ($attribute['type'])
                                            @case('boolean')
                                                <span
                                                    v-text="product.product['{{ $attribute['code'] }}']
                                                            ? '{{ __('velocity::app.shop.general.yes') }}'
                                                            : '{{ __('velocity::app.shop.general.no') }}'"
                                                ></span>
                                                @break;
                                            @default
                                                <span v-html="product['{{ $attribute['code'] }}'] ? product['{{ $attribute['code'] }}'] : product.product['{{ $attribute['code'] }}'] ? product.product['{{ $attribute['code'] }}'] : '__'" class="fs16"></span>
                                                @break;
                                        @endswitch

                                        @break

                                @endswitch
                            </td>
                        </tr>
                    @endforeach
                </template>

                <span v-else-if="isProductListLoaded && products.length == 0" class="col-12">
                    @{{ __('customer.compare.empty-text') }}
                </span>
            </table>

            {!! view_render_event('bagisto.shop.customers.account.compare.view.after') !!}
        </section>
    </script>

    <script>
        Vue.component('compare-product', {
            template: '#compare-product-template',

            data: function () {
                return {
                    'products': [],
                    'isProductListLoaded': false,
                    'isCustomer': '{{ auth()->guard('customer')->user() ? "true" : "false" }}' == "true",
                }
            },

            mounted: function () {
                this.getComparedProducts();
            },

            methods: {
                'getComparedProducts': function () {
                    let items = '';
                    let url = `${this.$root.baseUrl}/${this.isCustomer ? 'comparison' : 'detailed-products'}`;

                    let data = {
                        params: {'data': true}
                    }

                    if (! this.isCustomer) {
                        items = this.getStorageValue('compared_product');
                        items = items ? items.join('&') : '';

                        data = {
                            params: {
                                items
                            }
                        };
                    }

                    if (this.isCustomer || (! this.isCustomer && items != "")) {
                        this.$http.get(url, data)
                        .then(response => {
                            this.isProductListLoaded = true;

                            if (response.data.products.length > 3) {
                                $('.compare-products').css('overflow-x', 'scroll');
                            }

                            this.products = response.data.products;
                        })
                        .catch(error => {
                            this.isProductListLoaded = true;
                            console.log(this.__('error.something_went_wrong'));
                        });
                    } else {
                        this.isProductListLoaded = true;
                    }

                },

                'removeProductCompare': function (productId) {
                    if (this.isCustomer) {
                        this.$http.delete(`${this.$root.baseUrl}/comparison?productId=${productId}`)
                        .then(response => {
                            if (productId == 'all') {
                                this.$set(this, 'products', this.products.filter(product => false));
                            } else {
                                this.$set(this, 'products', this.products.filter(product => product.id != productId));
                            }

                            window.showAlert(`alert-${response.data.status}`, response.data.label, response.data.message);
                        })
                        .catch(error => {
                            console.log(this.__('error.something_went_wrong'));
                        });
                    } else {
                        let existingItems = this.getStorageValue('compared_product');

                        if (productId == "all") {
                            updatedItems = [];
                            this.$set(this, 'products', []);
                        } else {
                            updatedItems = existingItems.filter(item => item != productId);
                            this.$set(this, 'products', this.products.filter(product => product.id != productId));
                        }

                        this.setStorageValue('compared_product', updatedItems);

                        window.showAlert(
                            `alert-success`,
                            this.__('shop.general.alert.success'),
                            `${this.__('customer.compare.removed')}`
                        );
                    }

                    this.$root.headerItemsCount++;
                },
            }
        });
    </script>
@endpush