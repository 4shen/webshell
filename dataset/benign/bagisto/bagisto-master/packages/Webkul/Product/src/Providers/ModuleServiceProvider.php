<?php

namespace Webkul\Product\Providers;

use Konekt\Concord\BaseModuleServiceProvider;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected $models = [
        \Webkul\Product\Models\Product::class,
        \Webkul\Product\Models\ProductAttributeValue::class,
        \Webkul\Product\Models\ProductFlat::class,
        \Webkul\Product\Models\ProductImage::class,
        \Webkul\Product\Models\ProductInventory::class,
        \Webkul\Product\Models\ProductOrderedInventory::class,
        \Webkul\Product\Models\ProductReview::class,
        \Webkul\Product\Models\ProductSalableInventory::class,
        \Webkul\Product\Models\ProductDownloadableSample::class,
        \Webkul\Product\Models\ProductDownloadableLink::class,
        \Webkul\Product\Models\ProductGroupedProduct::class,
        \Webkul\Product\Models\ProductBundleOption::class,
        \Webkul\Product\Models\ProductBundleOptionTranslation::class,
        \Webkul\Product\Models\ProductBundleOptionProduct::class,
        \Webkul\Product\Models\ProductCustomerGroupPrice::class,
    ];
}