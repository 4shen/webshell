<?php

namespace Webkul\Velocity\Helpers;

use Illuminate\Support\Facades\DB;
use Webkul\Product\Helpers\Review;
use Webkul\Product\Models\Product as ProductModel;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\Product\Repositories\ProductFlatRepository;
use Webkul\Velocity\Repositories\OrderBrandsRepository;
use Webkul\Attribute\Repositories\AttributeOptionRepository;
use Webkul\Product\Repositories\ProductReviewRepository;
use Webkul\Velocity\Repositories\VelocityMetadataRepository;

class Helper extends Review
{
    /**
     * productModel object
     *
     * @var \Webkul\Product\Contracts\Product
     */
   protected $productModel;

    /**
     * orderBrands object
     *
     * @var \Webkul\Velocity\Repositories\OrderBrandsRepository
     */
    protected $orderBrandsRepository;

    /**
     * ProductRepository object
     *
     * @var \Webkul\Product\Repositories\ProductRepository
     */
    protected $productRepository;

    /**
     * ProductFlatRepository object
     *
     * @var \Webkul\Product\Repositories\ProductFlatRepository
     */
    protected $productFlatRepository;

    /**
     * productModel object
     *
     * @var \Webkul\Attribute\Repositories\AttributeOptionRepository
     */
    protected $attributeOptionRepository;

    /**
     * ProductReviewRepository object
     *
     * @var \Webkul\Product\Repositories\ProductReviewRepository
     */
    protected $productReviewRepository;

    /**
     * VelocityMetadata object
     *
     * @var \Webkul\Velocity\Repositories\VelocityMetadataRepository
     */
    protected $velocityMetadataRepository;

    /**
     * Create a helper instamce
     *
     * @param  \Webkul\Product\Contracts\Product                        $productModel
     * @param  \Webkul\Velocity\Repositories\OrderBrandsRepository      $orderBrands
     * @param  \Webkul\Attribute\Repositories\AttributeOptionRepository $attributeOptionRepository
     * @param  \Webkul\Product\Repositories\ProductReviewRepository     $productReviewRepository
     * @param  \Webkul\Velocity\Repositories\VelocityMetadataRepository $velocityMetadataRepository
     *
     * @return void
     */
    public function __construct(
        ProductModel $productModel,
        ProductRepository $productRepository,
        AttributeOptionRepository $attributeOptionRepository,
        ProductFlatRepository $productFlatRepository,
        OrderBrandsRepository $orderBrandsRepository,
        ProductReviewRepository $productReviewRepository,
        VelocityMetadataRepository $velocityMetadataRepository
    ) {
        $this->productModel =  $productModel;

        $this->attributeOptionRepository =  $attributeOptionRepository;

        $this->productRepository = $productRepository;

        $this->productFlatRepository = $productFlatRepository;

        $this->orderBrandsRepository = $orderBrandsRepository;

        $this->productReviewRepository =  $productReviewRepository;

        $this->velocityMetadataRepository =  $velocityMetadataRepository;
    }

    /**
     * @param  \Webkul\Sales\Contracts\Order $order
     *
     * @return void
     */
    public function topBrand($order)
    {
        $orderItems = $order->items;

        foreach ($orderItems as $key => $orderItem) {
            $products[] = $orderItem->product;

            try {
                $this->orderBrandsRepository->create([
                    'order_item_id' => $orderItem->id,
                    'order_id'      => $orderItem->order_id,
                    'product_id'    => $orderItem->product_id,
                    'brand'         => $products[$key]->brand,
                ]);
            } catch(\Exception $exception) {}
        }
    }

    /**
     * @return \Illuminate\Support\Collection|\Exception
     */
    public function getBrandsWithCategories()
    {
        try {
            $orderBrand = $this->orderBrandsRepository->get()->toArray();

            if (isset($orderBrand) && ! empty($orderBrand)) {
                foreach ($orderBrand as $product) {
                    $product_id[] = $product['product_id'];

                    $product_categories = $this->productRepository->with('categories')->findWhereIn('id', $product_id)->toArray();
                }

                $categoryName = $brandName = $brandImplode = [];

                foreach($product_categories as $totalData) {
                    $brand = $this->attributeOptionRepository->findOneWhere(['id' => $totalData['brand']]);

                    foreach ($totalData['categories'] as $categories) {
                        foreach($categories['translations'] as $catName) {
                            if (isset($brand->admin_name)) {
                                $brandData[$brand->admin_name][] = $catName['name'];
                                $categoryName[] = $catName['name'];
                            }
                        }
                    }
                }

                $uniqueCategoryName = array_unique($categoryName);

                foreach($uniqueCategoryName as $key => $categoryNameValue) {
                    foreach($brandData as $brandDataKey => $brandDataValue) {
                        if(in_array($categoryNameValue,$brandDataValue)) {
                            $brandName[$categoryNameValue][] = $brandDataKey;
                        }
                    }
                }

                foreach($brandName as $brandKey => $brandvalue) {
                    $brandImplode[$brandKey][] = implode(' | ',array_map("ucfirst", $brandvalue));
                }

                return $brandImplode;
            }
        } catch (Exception $exception){
            throw $exception;
        }
    }

    /**
     * Returns the count rating of the product
     *
     * @param  \Webkul\Product\Contracts\Product  $product
     *
     * @return int
     */
    public function getCountRating($product)
    {
        $reviews = $product->reviews()
                           ->where('status', 'approved')
                           ->select('rating', DB::raw('count(*) as total'))
                           ->groupBy('rating')
                           ->orderBy('rating','desc')
                           ->get();

        $totalReviews = $this->getTotalReviews($product);

        for ($i = 5; $i >= 1; $i--) {
            if (! $reviews->isEmpty()) {
                foreach ($reviews as $review) {
                    if ($review->rating == $i) {
                        $percentage[$i] = $review->total;

                        break;
                    } else {
                        $percentage[$i]=0;
                    }
                }
            } else {
                $percentage[$i]=0;
            }
        }

        return $percentage;
    }

    /**
     * Returns the count rating of the product
     *
     * @return array
     */
    public function getVelocityMetaData($locale = null, $default = true)
    {
        if (! $locale) {
            $locale = request()->get('locale') ?: app()->getLocale();
        }

        try {
            $metaData = $this->velocityMetadataRepository->findOneWhere([
                'locale' => $locale
            ]);

            if (! $metaData && $default) {
                $metaData = $this->velocityMetadataRepository->findOneWhere([
                    'locale' => 'en'
                ]);
            }

            return $metaData;
        } catch (\Exception $exception) {
        }
    }

    /**
     * @param  int  $reviewCount
     * @return \Illuminate\Support\Collection
     */
    public function getShopRecentReviews($reviewCount = 4)
    {
        $reviews = $this->productReviewRepository
                        ->getModel()
                        ->orderBy('id', 'desc')
                        ->where('status', 'approved')
                        ->take($reviewCount)->get();

        return $reviews;
    }

    /**
     * @return array
     */
    public function jsonTranslations()
    {
        $currentLocale = app()->getLocale();

        $path = __DIR__ . "/../Resources/lang/$currentLocale/app.php";

        if (is_string($path) && is_readable($path)) {
            return include $path;
        } else {
            $currentLocale = "en";

            $path = __DIR__ . "/../Resources/lang/$currentLocale/app.php";

            return include $path;
        }
    }

    /**
     * @param  \Webkul\Checkout\Contracts\CartItem  $item
     * @return array
     */
    public function formatCartItem($item)
    {
        $product = $item->product;

        $images = $product->getTypeInstance()->getBaseImage($item);

        return [
            'images'    => $images,
            'itemId'    => $item->id,
            'name'      => $item->name,
            'quantity'  => $item->quantity,
            'url_key'   => $product->url_key,
            'baseTotal' => core()->currency($item->base_total),
        ];
    }

    /**
     * @param  \Webkul\Product\Contracts\Product  $product
     * @param  bool                               $list
     * @param  array                              $metaInformation
     * 
     * @return array
     */
    public function formatProduct($product, $list = false, $metaInformation = [])
    {
        $reviewHelper = app('Webkul\Product\Helpers\Review');
        $productImageHelper = app('Webkul\Product\Helpers\ProductImage');

        $totalReviews = $reviewHelper->getTotalReviews($product);

        $avgRatings = ceil($reviewHelper->getAverageRating($product));

        $galleryImages = $productImageHelper->getGalleryImages($product);
        $productImage = $productImageHelper->getProductBaseImage($product)['medium_image_url'];

        $largeProductImageName = "large-product-placeholder.png";
        $mediumProductImageName = "meduim-product-placeholder.png";

        if (strpos($productImage, $mediumProductImageName) > -1) {
            $productImageNameCollection = explode('/', $productImage);
            $productImageName = $productImageNameCollection[sizeof($productImageNameCollection) - 1];

            if ($productImageName == $mediumProductImageName) {
                $productImage = str_replace($mediumProductImageName, $largeProductImageName, $productImage);
            }
        }

        return [
            'avgRating'         => $avgRatings,
            'totalReviews'      => $totalReviews,
            'image'             => $productImage,
            'galleryImages'     => $galleryImages,
            'name'              => $product->name,
            'slug'              => $product->url_key,
            'description'       => $product->description,
            'shortDescription'  => $product->short_description,
            'firstReviewText'   => trans('velocity::app.products.be-first-review'),
            'new'               => $product->new ? __('shop::app.products.new') : false,
            'priceHTML'         => view('shop::products.price', ['product' => $product])->render(),
            'defaultAddToCart'  => view('shop::products.add-buttons', ['product' => $product])->render(),
            'addToCartHtml'     => view('shop::products.add-to-cart', [
                'product'           => $product,
                'addWishlistClass'  => ! (isset($list) && $list) ? '' : '',

                'showCompare'       => core()->getConfigData('general.content.shop.compare_option') == "1"
                                       ? true : false,

                'btnText'           => (isset($metaInformation['btnText']) && $metaInformation['btnText'])
                                       ? $metaInformation['btnText'] : null,

                'moveToCart'        => (isset($metaInformation['moveToCart']) && $metaInformation['moveToCart'])
                                       ? $metaInformation['moveToCart'] : null,

                'addToCartBtnClass' => ! (isset($list) && $list) ? 'small-padding' : '',
            ])->render(),
        ];
    }

    /**
     * Returns the count rating of the product
     *
     * @param $items
     * @param $separator
     *
     * @return array
     */
    public function fetchProductCollection($items, $moveToCart = false, $separator='&')
    {
        $productCollection = [];
        $productIds = explode($separator, $items);

        foreach ($productIds as $productId) {
            // @TODO:- query only once insted of 2
            $productFlat = $this->productFlatRepository->findOneWhere(['id' => $productId]);

            if ($productFlat) {
                $product = $this->productRepository->findOneWhere(['id' => $productFlat->product_id]);

                if ($product) {
                    $formattedProduct = $this->formatProduct($productFlat, false, [
                        'moveToCart' => $moveToCart,
                        'btnText' => $moveToCart ? trans('shop::app.customer.account.wishlist.move-to-cart') : null,
                    ]);

                    $productMetaDetails = [];
                    $productMetaDetails['slug'] = $product->url_key;
                    $productMetaDetails['image'] = $formattedProduct['image'];
                    $productMetaDetails['priceHTML'] = $formattedProduct['priceHTML'];
                    $productMetaDetails['addToCartHtml'] = $formattedProduct['addToCartHtml'];
                    $productMetaDetails['galleryImages'] = $formattedProduct['galleryImages'];
                    $productMetaDetails['defaultAddToCart'] = $formattedProduct['defaultAddToCart'];

                    $product = array_merge($productFlat->toArray(), $productMetaDetails);

                    array_push($productCollection, $product);
                }
            }
        }

        return $productCollection;
    }
}
