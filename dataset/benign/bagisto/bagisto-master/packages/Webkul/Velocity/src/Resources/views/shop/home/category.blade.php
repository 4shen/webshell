@inject ('productRepository', 'Webkul\Product\Repositories\ProductRepository')

<category-products
    category-slug="{{ $category }}"
></category-products>

@push('scripts')
    <script type="text/x-template" id="category-products-template">
        <div class="container-fluid remove-padding-margin">
            <shimmer-component v-if="isLoading && !isMobileView"></shimmer-component>

            <template v-else-if="categoryProducts.length > 0">
                <card-list-header
                    :heading="categoryDetails.name"
                    :view-all="`${this.baseUrl}/${categoryDetails.slug}`">
                </card-list-header>

                <div class="carousel-products vc-full-screen ltr" v-if="!isMobileView">
                    <carousel-component
                        slides-per-page="6"
                        navigation-enabled="hide"
                        pagination-enabled="hide"
                        :slides-count="categoryProducts.length"
                        :id="`${categoryDetails.name}-carousel`">

                            <slide
                                :key="index"
                                :slot="`slide-${index}`"
                                v-for="(product, index) in categoryProducts">
                                <product-card
                                    :list="list"
                                    :product="product">
                                </product-card>
                            </slide>
                    </carousel-component>
                </div>

                <div class="carousel-products vc-small-screen" v-else>
                    <carousel-component
                        slides-per-page="2"
                        navigation-enabled="hide"
                        pagination-enabled="hide"
                        :slides-count="categoryProducts.length"
                        :id="`${categoryDetails.name}-carousel`">

                        <slide
                            :key="index"
                            :slot="`slide-${index}`"
                            v-for="(product, index) in categoryProducts">
                            <product-card
                                :list="list"
                                :product="product">
                            </product-card>
                        </slide>
                    </carousel-component>
                </div>
            </template>

        </div>
    </script>

    <script type="text/javascript">
        (() => {
            Vue.component('category-products', {
                template: '#category-products-template',
                props: [
                    'categorySlug',
                ],

                data: function () {
                    return {
                        isLoading: true,
                        isCategory: false,
                        heading: 'customer',
                        categoryProducts: [],
                        isMobileView: this.$root.isMobile(),
                    }
                },

                mounted: function () {
                    this.getCategoryDetails();
                },

                methods: {
                    'getCategoryDetails': function () {
                        this.$http.get(`${this.baseUrl}/category-details?category-slug=${this.categorySlug}`)
                        .then(response => {
                            if (response.data.status) {
                                this.list = response.data.list;
                                this.categoryDetails = response.data.categoryDetails;
                                this.categoryProducts = response.data.categoryProducts;

                                this.isCategory = true;

                                // setTimeout(() => {
                                //     let imagesCollection = document.querySelectorAll('img.lzy_img');
                                //     imagesCollection.forEach((image) => {
                                //         this.$root.imageObserver.observe(image);
                                //     });
                                // }, 0);
                            }

                            this.isLoading = false;
                        })
                        .catch(error => {
                            this.isLoading = false;
                            console.log(this.__('error.something_went_wrong'));
                        });
                    }
                }
            })
        })()
    </script>
@endpush