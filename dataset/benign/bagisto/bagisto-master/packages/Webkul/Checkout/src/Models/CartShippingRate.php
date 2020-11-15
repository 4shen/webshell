<?php

namespace Webkul\Checkout\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Checkout\Contracts\CartShippingRate as CartShippingRateContract;

class CartShippingRate extends Model implements CartShippingRateContract
{
    protected $fillable = [
        'carrier',
        'carrier_title',
        'method',
        'method_title',
        'method_description',
        'price',
        'base_price',
        'discount_amount',
        'base_discount_amount',
    ];

    /**
     * Get the post that owns the comment.
     */
    public function shipping_address()
    {
        return $this->belongsTo(CartAddressProxy::modelClass(), 'cart_address_id')
            ->where('address_type', CartAddress::ADDRESS_TYPE_SHIPPING);
    }
}