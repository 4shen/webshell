<?php

namespace Webkul\Checkout;

use Exception;
use Illuminate\Support\Facades\Log;
use Webkul\Checkout\Models\Cart as CartModel;
use Webkul\Checkout\Models\CartAddress;
use Webkul\Checkout\Repositories\CartRepository;
use Webkul\Checkout\Repositories\CartItemRepository;
use Webkul\Checkout\Repositories\CartAddressRepository;
use Webkul\Customer\Models\CustomerAddress;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\Tax\Helpers\Tax;
use Webkul\Tax\Repositories\TaxCategoryRepository;
use Webkul\Checkout\Models\CartItem;
use Webkul\Checkout\Models\CartPayment;
use Webkul\Customer\Repositories\WishlistRepository;
use Webkul\Customer\Repositories\CustomerAddressRepository;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Arr;

class Cart
{

    /**
     * CartRepository instance
     *
     * @var \Webkul\Checkout\Repositories\CartRepository
     */
    protected $cartRepository;

    /**
     * CartItemRepository instance
     *
     * @var \Webkul\Checkout\Repositories\CartItemRepository
     */
    protected $cartItemRepository;

    /**
     * CartAddressRepository instance
     *
     * @var \Webkul\Checkout\Repositories\CartAddressRepository
     */
    protected $cartAddressRepository;

    /**
     * ProductRepository instance
     *
     * @var \Webkul\Checkout\Repositories\ProductRepository
     */
    protected $productRepository;

    /**
     * TaxCategoryRepository instance
     *
     * @var \Webkul\Tax\Repositories\TaxCategoryRepository
     */
    protected $taxCategoryRepository;

    /**
     * WishlistRepository instance
     *
     * @var \Webkul\Customer\Repositories\WishlistRepository
     */
    protected $wishlistRepository;

    /**
     * CustomerAddressRepository instance
     *
     * @var \Webkul\Customer\Repositories\CustomerAddressRepository
     */
    protected $customerAddressRepository;

    /**
     * Create a new class instance.
     *
     * @param  \Webkul\Checkout\Repositories\CartRepository             $cartRepository
     * @param  \Webkul\Checkout\Repositories\CartItemRepository         $cartItemRepository
     * @param  \Webkul\Checkout\Repositories\CartAddressRepository      $cartAddressRepository
     * @param  \Webkul\Product\Repositories\ProductRepository           $productRepository
     * @param  \Webkul\Tax\Repositories\TaxCategoryRepository           $taxCategoryRepository
     * @param  \Webkul\Customer\Repositories\WishlistRepository         $wishlistRepository
     * @param  \Webkul\Customer\Repositories\CustomerAddressRepository  $customerAddressRepository
     * @return void
     */
    public function __construct(
        CartRepository $cartRepository,
        CartItemRepository $cartItemRepository,
        CartAddressRepository $cartAddressRepository,
        ProductRepository $productRepository,
        TaxCategoryRepository $taxCategoryRepository,
        WishlistRepository $wishlistRepository,
        CustomerAddressRepository $customerAddressRepository
    )
    {
        $this->cartRepository = $cartRepository;

        $this->cartItemRepository = $cartItemRepository;

        $this->cartAddressRepository = $cartAddressRepository;

        $this->productRepository = $productRepository;

        $this->taxCategoryRepository = $taxCategoryRepository;

        $this->wishlistRepository = $wishlistRepository;

        $this->customerAddressRepository = $customerAddressRepository;
    }

    /**
     * Return current logged in customer
     *
     * @return \Webkul\Customer\Contracts\Customer|bool
     */
    public function getCurrentCustomer()
    {
        $guard = request()->has('token') ? 'api' : 'customer';

        return auth()->guard($guard);
    }

    /**
     * Add Items in a cart with some cart and item details.
     *
     * @param int   $productId
     * @param array $data
     *
     * @return \Webkul\Checkout\Contracts\Cart|string|array
     * @throws Exception
     */
    public function addProduct($productId, $data)
    {
        Event::dispatch('checkout.cart.add.before', $productId);

        $cart = $this->getCart();

        if (! $cart && ! $cart = $this->create($data)) {
            return ['warning' => __('shop::app.checkout.cart.item.error-add')];
        }

        $product = $this->productRepository->findOneByField('id', $productId);

        $cartProducts = $product->getTypeInstance()->prepareForCart($data);

        if (is_string($cartProducts)) {
            $this->collectTotals();

            if (count($cart->all_items) <= 0) {
                session()->forget('cart');
            }

            throw new Exception($cartProducts);
        } else {
            $parentCartItem = null;

            foreach ($cartProducts as $cartProduct) {
                $cartItem = $this->getItemByProduct($cartProduct);

                if (isset($cartProduct['parent_id'])) {
                    $cartProduct['parent_id'] = $parentCartItem->id;
                }

                if (! $cartItem) {
                    $cartItem = $this->cartItemRepository->create(array_merge($cartProduct, ['cart_id' => $cart->id]));
                } else {
                    if (isset($cartProduct['parent_id']) && $cartItem->parent_id !== $parentCartItem->id) {
                        $cartItem = $this->cartItemRepository->create(array_merge($cartProduct, [
                            'cart_id' => $cart->id
                        ]));
                    } else {
                        // if ($cartItem->product->getTypeInstance()->showQuantityBox() === false) {
                        //     return ['warning' => __('shop::app.checkout.cart.integrity.qty_impossible')];
                        // }

                        $cartItem = $this->cartItemRepository->update($cartProduct, $cartItem->id);
                    }
                }

                if (! $parentCartItem) {
                    $parentCartItem = $cartItem;
                }
            }
        }

        Event::dispatch('checkout.cart.add.after', $cart);

        $this->collectTotals();

        return $this->getCart();
    }

    /**
     * Create new cart instance.
     *
     * @param  array  $data
     * @return \Webkul\Checkout\Contracts\Cart|null
     */
    public function create($data)
    {
        $cartData = [
            'channel_id'            => core()->getCurrentChannel()->id,
            'global_currency_code'  => core()->getBaseCurrencyCode(),
            'base_currency_code'    => core()->getBaseCurrencyCode(),
            'channel_currency_code' => core()->getChannelBaseCurrencyCode(),
            'cart_currency_code'    => core()->getCurrentCurrencyCode(),
            'items_count'           => 1,
        ];

        // Fill in the customer data, as far as possible:
        if ($this->getCurrentCustomer()->check()) {
            $cartData['customer_id'] = $this->getCurrentCustomer()->user()->id;
            $cartData['is_guest'] = 0;
            $cartData['customer_first_name'] = $this->getCurrentCustomer()->user()->first_name;
            $cartData['customer_last_name'] = $this->getCurrentCustomer()->user()->last_name;
            $cartData['customer_email'] = $this->getCurrentCustomer()->user()->email;
        } else {
            $cartData['is_guest'] = 1;
        }

        $cart = $this->cartRepository->create($cartData);

        if (! $cart) {
            session()->flash('error', __('shop::app.checkout.cart.create-error'));

            return;
        }

        $this->putCart($cart);

        return $cart;
    }

    /**
     * Update cart items information
     *
     * @param  array  $data
     *
     * @return bool|void|Exception
     */
    public function updateItems($data)
    {
        foreach ($data['qty'] as $itemId => $quantity) {
            $item = $this->cartItemRepository->findOneByField('id', $itemId);

            if (! $item) {
                continue;
            }

            if ($quantity <= 0) {
                $this->removeItem($itemId);

                throw new Exception(__('shop::app.checkout.cart.quantity.illegal'));
            }

            $item->quantity = $quantity;

            if (! $this->isItemHaveQuantity($item)) {
                throw new Exception(__('shop::app.checkout.cart.quantity.inventory_warning'));
            }

            Event::dispatch('checkout.cart.update.before', $item);

            $this->cartItemRepository->update([
                'quantity'          => $quantity,
                'total'             => core()->convertPrice($item->price * $quantity),
                'base_total'        => $item->price * $quantity,
                'total_weight'      => $item->weight * $quantity,
                'base_total_weight' => $item->weight * $quantity,
            ], $itemId);

            Event::dispatch('checkout.cart.update.after', $item);
        }

        $this->collectTotals();

        return true;
    }

    /**
     * Get cart item by product
     *
     * @param  array  $data
     * @return \Webkul\Checkout\Contracts\CartItem|void
     */
    public function getItemByProduct($data)
    {
        $items = $this->getCart()->all_items;

        foreach ($items as $item) {
            if ($item->product->getTypeInstance()->compareOptions($item->additional, $data['additional'])) {
                if (isset($data['additional']['parent_id'])) {
                    if ($item->parent->product->getTypeInstance()->compareOptions($item->parent->additional, request()->all())) {
                        return $item;
                    }
                } else {
                    return $item;
                }
            }
        }
    }

    /**
     * Remove the item from the cart
     *
     * @param  int  $itemId
     * @return boolean
     */
    public function removeItem($itemId)
    {
        Event::dispatch('checkout.cart.delete.before', $itemId);

        if (! $cart = $this->getCart()) {
            return false;
        }

        $this->cartItemRepository->delete($itemId);

        if ($cart->items()->get()->count() == 0) {
            $this->cartRepository->delete($cart->id);

            if (session()->has('cart')) {
                session()->forget('cart');
            }
        }

        Event::dispatch('checkout.cart.delete.after', $itemId);

        $this->collectTotals();

        return true;
    }

    /**
     * This function handles when guest has some of cart products and then logs in.
     *
     * @return bool
     */
    public function mergeCart()
    {
        if (session()->has('cart')) {
            $cart = $this->cartRepository->findOneWhere([
                'customer_id' => $this->getCurrentCustomer()->user()->id,
                'is_active'   => 1,
            ]);

            $guestCart = session()->get('cart');

            //when the logged in customer is not having any of the cart instance previously and are active.
            if (! $cart) {
                $this->cartRepository->update([
                    'customer_id'         => $this->getCurrentCustomer()->user()->id,
                    'is_guest'            => 0,
                    'customer_first_name' => $this->getCurrentCustomer()->user()->first_name,
                    'customer_last_name'  => $this->getCurrentCustomer()->user()->last_name,
                    'customer_email'      => $this->getCurrentCustomer()->user()->email,
                ], $guestCart->id);

                session()->forget('cart');

                return true;
            }

            foreach ($guestCart->items as $key => $guestCartItem) {
                $found = false;

                foreach ($cart->items as $cartItem) {
                    if (! $cartItem
                        ->product
                        ->getTypeInstance()
                        ->compareOptions($cartItem->additional, $guestCartItem->additional)
                    ) {
                        continue;
                    }

                    $found = true;

                    $cartItem->quantity = $newQuantity = $cartItem->quantity + $guestCartItem->quantity;

                    if ($cartItem->quantity > $cartItem->product->getTypeInstance()->totalQuantity()) {
                        $cartItem->quantity = $newQuantity = $cartItem->product->getTypeInstance()->totalQuantity();
                    }

                    if (! $this->isItemHaveQuantity($cartItem)) {
                        $this->cartItemRepository->delete($guestCartItem->id);

                        continue;
                    }

                    $this->cartItemRepository->update([
                        'quantity'          => $newQuantity,
                        'total'             => core()->convertPrice($cartItem->price * $newQuantity),
                        'base_total'        => $cartItem->price * $newQuantity,
                        'total_weight'      => $cartItem->weight * $newQuantity,
                        'base_total_weight' => $cartItem->weight * $newQuantity,
                    ], $cartItem->id);

                    $guestCart->items->forget($key);

                    $this->cartItemRepository->delete($guestCartItem->id);
                }

                if (! $found) {
                    $this->cartItemRepository->update([
                        'cart_id' => $cart->id,
                    ], $guestCartItem->id);

                    foreach ($guestCartItem->children as $child) {
                        $this->cartItemRepository->update([
                            'cart_id' => $cart->id,
                        ], $child->id);
                    }
                }
            }

            $this->collectTotals();

            $this->cartRepository->delete($guestCart->id);

            session()->forget('cart');
        }

        return true;
    }

    /**
     * Save cart
     *
     * @param  \Webkul\Checkout\Contracts\Cart  $cart
     * @return void
     */
    public function putCart($cart)
    {
        if (! $this->getCurrentCustomer()->check()) {
            session()->put('cart', $cart);
        }
    }

    /**
     * Returns cart
     *
     * @return \Webkul\Checkout\Contracts\Cart|null
     */
    public function getCart(): ?\Webkul\Checkout\Contracts\Cart
    {
        if ($this->getCurrentCustomer()->check()) {
            return $this->cartRepository->findOneWhere([
                'customer_id' => $this->getCurrentCustomer()->user()->id,
                'is_active'   => 1,
            ]);

        } elseif (session()->has('cart')) {
            return $this->cartRepository->find(session()->get('cart')->id);
        }

        return null;
    }

    /**
     * Returns cart details in array
     *
     * @return array
     */
    public function toArray()
    {
        $cart = $this->getCart();

        $data = $cart->toArray();

        $data['billing_address'] = $cart->billing_address->toArray();

        if ($cart->haveStockableItems()) {
            $data['shipping_address'] = $cart->shipping_address->toArray();

            $data['selected_shipping_rate'] = $cart->selected_shipping_rate ? $cart->selected_shipping_rate->toArray() : 0.0;
        }

        $data['payment'] = $cart->payment->toArray();

        $data['items'] = $cart->items->toArray();

        return $data;
    }

    /**
     * Save customer address
     *
     * @param array $data
     *
     * @return bool
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    public function saveCustomerAddress($data): bool
    {
        if (! $cart = $this->getCart()) {
            return false;
        }

        $billingAddressData = $this->gatherBillingAddress($data, $cart);

        $shippingAddressData = $this->gatherShippingAddress($data, $cart);

        $this->saveAddressesWhenRequested($data, $billingAddressData, $shippingAddressData);

        $this->linkAddresses($cart, $billingAddressData, $shippingAddressData);

        $this->assignCustomerFields($cart);

        $cart->save();

        $this->collectTotals();

        return true;
    }

    /**
     * Save shipping method for cart
     *
     * @param  string  $shippingMethodCode
     * @return bool
     */
    public function saveShippingMethod($shippingMethodCode): bool
    {
        if (! $cart = $this->getCart()) {
            return false;
        }

        $cart->shipping_method = $shippingMethodCode;
        $cart->save();

        return true;
    }

    /**
     * Save payment method for cart
     *
     * @param  string  $payment
     * @return \Webkul\Checkout\Contracts\CartPayment
     */
    public function savePaymentMethod($payment)
    {
        if (! $cart = $this->getCart()) {
            return false;
        }

        if ($cartPayment = $cart->payment) {
            $cartPayment->delete();
        }

        $cartPayment = new CartPayment;

        $cartPayment->method = $payment['method'];
        $cartPayment->cart_id = $cart->id;
        $cartPayment->save();

        return $cartPayment;
    }

    /**
     * Updates cart totals
     *
     * @return void
     */
    public function collectTotals()
    {
        $validated = $this->validateItems();

        if (! $validated) {
            return false;
        }

        if (! $cart = $this->getCart()) {
            return false;
        }

        Event::dispatch('checkout.cart.collect.totals.before', $cart);

        $this->calculateItemsTax();
        $cart->refresh();

        $cart->grand_total = $cart->base_grand_total = 0;
        $cart->sub_total = $cart->base_sub_total = 0;
        $cart->tax_total = $cart->base_tax_total = 0;
        $cart->discount_amount = $cart->base_discount_amount = 0;

        foreach ($cart->items()->get() as $item) {
            $cart->discount_amount += $item->discount_amount;
            $cart->base_discount_amount += $item->base_discount_amount;

            $cart->sub_total = (float)$cart->sub_total + $item->total;
            $cart->base_sub_total = (float)$cart->base_sub_total + $item->base_total;
        }

        $cart->tax_total = Tax::getTaxTotal($cart, false);
        $cart->base_tax_total = Tax::getTaxTotal($cart, true);

        $cart->grand_total = $cart->sub_total + $cart->tax_total - $cart->discount_amount;
        $cart->base_grand_total = $cart->base_sub_total + $cart->base_tax_total - $cart->base_discount_amount;

        if ($shipping = $cart->selected_shipping_rate) {
            $cart->grand_total = (float)$cart->grand_total + $shipping->price - $shipping->discount_amount;
            $cart->base_grand_total = (float)$cart->base_grand_total + $shipping->base_price - $shipping->base_discount_amount;

            $cart->discount_amount += $shipping->discount_amount;
            $cart->base_discount_amount += $shipping->base_discount_amount;
        }

        $cart = $this->finalizeCartTotals($cart);

        $quantities = 0;

        foreach ($cart->items as $item) {
            $quantities = $quantities + $item->quantity;
        }

        $cart->items_count = $cart->items->count();

        $cart->items_qty = $quantities;

        $cart->save();

        Event::dispatch('checkout.cart.collect.totals.after', $cart);
    }

    /**
     * To validate if the product information is changed by admin and the items have been added to the cart before it.
     *
     * @return bool
     */
    public function validateItems()
    {
        if (! $cart = $this->getCart()) {
            return;
        }

        if (count($cart->items) == 0) {
            $this->cartRepository->delete($cart->id);

            return false;
        } else {
            foreach ($cart->items as $item) {
                $response = $item->product->getTypeInstance()->validateCartItem($item);
                // ToDo: refactoring of all validateCartItem functions, at the moment they return nothing

                if ($response) {
                    return;
                }

                $price = ! is_null($item->custom_price) ? $item->custom_price : $item->base_price;

                $this->cartItemRepository->update([
                    'price'      => core()->convertPrice($price),
                    'base_price' => $price,
                    'total'      => core()->convertPrice($price * $item->quantity),
                    'base_total' => $price * $item->quantity,
                ], $item->id);
            }

            return true;
        }
    }

    /**
     * Calculates cart items tax
     *
     * @return void
     */
    public function calculateItemsTax(): void
    {
        if (! $cart = $this->getCart()) {
            return;
        }

        Event::dispatch('checkout.cart.calculate.items.tax.before', $cart);

        foreach ($cart->items()->get() as $item) {
            $taxCategory = $this->taxCategoryRepository->find($item->product->tax_category_id);

            if (! $taxCategory) {
                continue;
            }

            if ($item->product->getTypeInstance()->isStockable()) {
                $address = $cart->shipping_address;
            } else {
                $address = $cart->billing_address;
            }

            if ($address === null && auth()->guard('customer')->check()) {
                $address = auth()->guard('customer')->user()->addresses()
                    ->where('default_address', 1)->first();
            }

            if ($address === null) {
                $address = new class() {
                    public $country;
                    public $state;
                    public $postcode;

                    function __construct()
                    {
                        $this->country = strtoupper(config('app.default_country'));
                    }
                };
            }

            $taxRates = $taxCategory->tax_rates()->where([
                'country' => $address->country,
            ])->orderBy('tax_rate', 'desc')->get();

            $item = $this->setItemTaxToZero($item);

            if ($taxRates->count()) {
                foreach ($taxRates as $rate) {
                    $haveTaxRate = false;

                    if ($rate->state != '' && $rate->state != $address->state) {
                        continue;
                    }

                    if (! $rate->is_zip) {
                        if ($rate->zip_code == '*' || $rate->zip_code == $address->postcode) {
                            $haveTaxRate = true;
                        }
                    } else {
                        if ($address->postcode >= $rate->zip_from && $address->postcode <= $rate->zip_to) {
                            $haveTaxRate = true;
                        }
                    }

                    if ($haveTaxRate) {
                        $item->tax_percent = $rate->tax_rate;
                        $item->tax_amount = ($item->total * $rate->tax_rate) / 100;
                        $item->base_tax_amount = ($item->base_total * $rate->tax_rate) / 100;

                        break;
                    }
                }
            }

            $item->save();
        }
        Event::dispatch('checkout.cart.calculate.items.tax.after', $cart);
    }

    /**
     * Set Item tax to zero.
     *
     * @param  \Webkul\Checkout\Contracts\CartItem  $item
     * @return \Webkul\Checkout\Contracts\CartItem
     */
    protected function setItemTaxToZero(\Webkul\Checkout\Contracts\CartItem $item): \Webkul\Checkout\Contracts\CartItem
    {
        $item->tax_percent = 0;
        $item->tax_amount = 0;
        $item->base_tax_amount = 0;

        return $item;
    }

    /**
     * Checks if cart has any error
     *
     * @return bool
     */
    public function hasError(): bool
    {
        if (! $this->getCart()) {
            return true;
        }

        if (! $this->isItemsHaveSufficientQuantity()) {
            return true;
        }

        return false;
    }

    /**
     * Checks if all cart items have sufficient quantity.
     *
     * @return bool
     */
    public function isItemsHaveSufficientQuantity(): bool
    {
        foreach ($this->getCart()->items as $item) {
            if (! $this->isItemHaveQuantity($item)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Checks if all cart items have sufficient quantity.
     *
     * @param \Webkul\Checkout\Contracts\CartItem  $item
     * @return bool
     */
    public function isItemHaveQuantity($item): bool
    {
        return $item->product->getTypeInstance()->isItemHaveQuantity($item);
    }

    /**
     * Deactivates current cart
     *
     * @return void
     */
    public function deActivateCart(): void
    {
        if ($cart = $this->getCart()) {
            $this->cartRepository->update(['is_active' => false], $cart->id);

            if (session()->has('cart')) {
                session()->forget('cart');
            }
        }
    }

    /**
     * Validate order before creation
     *
     * @return array
     */
    public function prepareDataForOrder(): array
    {
        $data = $this->toArray();

        $finalData = [
            'cart_id'               => $this->getCart()->id,
            'customer_id'           => $data['customer_id'],
            'is_guest'              => $data['is_guest'],
            'customer_email'        => $data['customer_email'],
            'customer_first_name'   => $data['customer_first_name'],
            'customer_last_name'    => $data['customer_last_name'],
            'customer'              => $this->getCurrentCustomer()->check() ? $this->getCurrentCustomer()->user() : null,
            'total_item_count'      => $data['items_count'],
            'total_qty_ordered'     => $data['items_qty'],
            'base_currency_code'    => $data['base_currency_code'],
            'channel_currency_code' => $data['channel_currency_code'],
            'order_currency_code'   => $data['cart_currency_code'],
            'grand_total'           => $data['grand_total'],
            'base_grand_total'      => $data['base_grand_total'],
            'sub_total'             => $data['sub_total'],
            'base_sub_total'        => $data['base_sub_total'],
            'tax_amount'            => $data['tax_total'],
            'base_tax_amount'       => $data['base_tax_total'],
            'coupon_code'           => $data['coupon_code'],
            'applied_cart_rule_ids' => $data['applied_cart_rule_ids'],
            'discount_amount'       => $data['discount_amount'],
            'base_discount_amount'  => $data['base_discount_amount'],
            'billing_address'       => Arr::except($data['billing_address'], ['id', 'cart_id']),
            'payment'               => Arr::except($data['payment'], ['id', 'cart_id']),
            'channel'               => core()->getCurrentChannel(),
        ];

        if ($this->getCart()->haveStockableItems()) {
            $finalData = array_merge($finalData, [
                'shipping_method'               => $data['selected_shipping_rate']['method'],
                'shipping_title'                => $data['selected_shipping_rate']['carrier_title'] . ' - ' . $data['selected_shipping_rate']['method_title'],
                'shipping_description'          => $data['selected_shipping_rate']['method_description'],
                'shipping_amount'               => $data['selected_shipping_rate']['price'],
                'base_shipping_amount'          => $data['selected_shipping_rate']['base_price'],
                'shipping_address'              => Arr::except($data['shipping_address'], ['id', 'cart_id']),
                'shipping_discount_amount'      => $data['selected_shipping_rate']['discount_amount'],
                'base_shipping_discount_amount' => $data['selected_shipping_rate']['base_discount_amount'],
            ]);

        }

        foreach ($data['items'] as $item) {
            $finalData['items'][] = $this->prepareDataForOrderItem($item);
        }

        return $finalData;
    }

    /**
     * Prepares data for order item
     *
     * @param  array  $data
     * @return array
     */
    public function prepareDataForOrderItem($data): array
    {
        $finalData = [
            'product'              => $this->productRepository->find($data['product_id']),
            'sku'                  => $data['sku'],
            'type'                 => $data['type'],
            'name'                 => $data['name'],
            'weight'               => $data['weight'],
            'total_weight'         => $data['total_weight'],
            'qty_ordered'          => $data['quantity'],
            'price'                => $data['price'],
            'base_price'           => $data['base_price'],
            'total'                => $data['total'],
            'base_total'           => $data['base_total'],
            'tax_percent'          => $data['tax_percent'],
            'tax_amount'           => $data['tax_amount'],
            'base_tax_amount'      => $data['base_tax_amount'],
            'discount_percent'     => $data['discount_percent'],
            'discount_amount'      => $data['discount_amount'],
            'base_discount_amount' => $data['base_discount_amount'],
            'additional'           => $data['additional'],
        ];

        if (isset($data['children']) && $data['children']) {
            foreach ($data['children'] as $child) {
                $child['quantity'] = $child['quantity'] ? $child['quantity'] * $data['quantity'] : $child['quantity'];

                $finalData['children'][] = $this->prepareDataForOrderItem($child);
            }
        }

        return $finalData;
    }

    /**
     * Move a wishlist item to cart
     *
     * @param  \Webkul\Customer\Contracts\WishlistItem  $wishlistItem
     * @return bool
     */
    public function moveToCart($wishlistItem)
    {
        if (! $wishlistItem->product->getTypeInstance()->canBeMovedFromWishlistToCart($wishlistItem)) {
            return false;
        }

        if (! $wishlistItem->additional) {
            $wishlistItem->additional = ['product_id' => $wishlistItem->product_id];
        }

        request()->merge($wishlistItem->additional);

        $result = $this->addProduct($wishlistItem->product_id, $wishlistItem->additional);

        if ($result) {
            $this->wishlistRepository->delete($wishlistItem->id);

            return true;
        }

        return false;
    }

    /**
     * Function to move a already added product to wishlist will run only on customer
     * authentication.
     *
     * @param  int  $itemId
     * @return bool
     */
    public function moveToWishlist($itemId)
    {
        $cart = $this->getCart();

        $cartItem = $cart->items()->find($itemId);

        if (! $cartItem) {
            return false;
        }

        $wishlistItems = $this->wishlistRepository->findWhere([
            'customer_id' => $this->getCurrentCustomer()->user()->id,
            'product_id'  => $cartItem->product_id,
        ]);

        $found = false;

        foreach ($wishlistItems as $wishlistItem) {
            $options = $wishlistItem->item_options;

            if (! $options) {
                $options = ['product_id' => $wishlistItem->product_id];
            }

            if ($cartItem->product->getTypeInstance()->compareOptions($cartItem->additional, $options)) {
                $found = true;
            }
        }

        if (! $found) {
            $this->wishlistRepository->create([
                'channel_id'  => $cart->channel_id,
                'customer_id' => $this->getCurrentCustomer()->user()->id,
                'product_id'  => $cartItem->product_id,
                'additional'  => $cartItem->additional,
            ]);
        }

        $result = $this->cartItemRepository->delete($itemId);

        if (! $cart->items()->count()) {
            $this->cartRepository->delete($cart->id);
        }

        $this->collectTotals();

        return true;
    }

    /**
     * Set coupon code to the cart
     *
     * @param  string  $code
     * @return \Webkul\Checkout\Contracts\Cart
     */
    public function setCouponCode($code)
    {
        $cart = $this->getCart();

        $cart->coupon_code = $code;

        $cart->save();

        return $this;
    }

    /**
     * Remove coupon code from cart
     *
     * @return \Webkul\Checkout\Contracts\Cart
     */
    public function removeCouponCode()
    {
        $cart = $this->getCart();

        $cart->coupon_code = null;

        $cart->save();

        return $this;
    }

    /**
     * Transfer the user profile information into the cart/into the order.
     *
     * When logged in as guest or the customer profile is not complete, we use the
     * billing address to fill the order customer_ data.
     *
     * @param \Webkul\Checkout\Contracts\Cart $cart
     */
    private function assignCustomerFields(\Webkul\Checkout\Contracts\Cart $cart): void
    {
        if ($this->getCurrentCustomer()->check()
            && ($user = $this->getCurrentCustomer()->user())
            && $this->profileIsComplete($user)
        ) {
            $cart->customer_email = $user->email;
            $cart->customer_first_name = $user->first_name;
            $cart->customer_last_name = $user->last_name;
        } else {
            $cart->customer_email = $cart->billing_address->email;
            $cart->customer_first_name = $cart->billing_address->first_name;
            $cart->customer_last_name = $cart->billing_address->last_name;
        }
    }

    /**
     * Round cart totals
     *
     * @param \Webkul\Checkout\Models\Cart $cart
     *
     * @return \Webkul\Checkout\Models\Cart
     */
    private function finalizeCartTotals(CartModel $cart): CartModel
    {
        $cart->discount_amount = round($cart->discount_amount, 2);
        $cart->base_discount_amount = round($cart->base_discount_amount, 2);

        $cart->grand_total = round($cart->grand_total, 2);
        $cart->grand_total = round($cart->grand_total, 2);
        $cart->base_grand_total = round($cart->base_grand_total, 2);

        return $cart;
    }

    /**
     * @param $user
     *
     * @return bool
     */
    private function profileIsComplete($user): bool
    {
        return $user->email && $user->first_name && $user->last_name;
    }

    /**
     * @return array
     */
    private function fillCustomerAttributes(): array
    {
        $attributes = [];

        $user = $this->getCurrentCustomer()->user();

        if ($user) {
            $attributes['first_name'] = $user->first_name;
            $attributes['last_name'] = $user->last_name;
            $attributes['email'] = $user->email;
            $attributes['customer_id'] = $user->id;
        }

        return $attributes;
    }

    /**
     * @return array
     */
    private function fillAddressAttributes(array $addressAttributes): array
    {
        $attributes = [];

        $cartAddress = new CartAddress();

        foreach ($cartAddress->getFillable() as $attribute) {
            if (isset($addressAttributes[$attribute])) {
                $attributes[$attribute] = $addressAttributes[$attribute];
            }
        }

        return $attributes;
    }

    /**
     * @param array $data
     * @param array $billingAddress
     * @param array $shippingAddress
     */
    private function saveAddressesWhenRequested(
        array $data,
        array $billingAddress,
        array $shippingAddress
    ): void
    {
        if (isset($data['billing']['save_as_address']) && $data['billing']['save_as_address']) {
            $this->customerAddressRepository->create($billingAddress);
        }

        if (isset($data['shipping']['save_as_address']) && $data['shipping']['save_as_address']) {
            $this->customerAddressRepository->create($shippingAddress);
        }
    }

    /**
     * @param $data
     * @param $cart
     *
     * @return array
     */
    private function gatherBillingAddress($data, \Webkul\Checkout\Models\Cart $cart): array
    {
        $customerAddress = [];

        if (isset($data['billing']['address_id']) && $data['billing']['address_id']) {
            $customerAddress = $this
                ->customerAddressRepository
                ->findOneWhere(['id' => $data['billing']['address_id']])
                ->toArray();
        }

        $billingAddress = array_merge(
            $customerAddress,
            $data['billing'],
            ['cart_id' => $cart->id],
            $this->fillCustomerAttributes(),
            $this->fillAddressAttributes($data['billing'])
        );


        return $billingAddress;
    }

    /**
     * @param                            $data
     * @param \Webkul\Checkout\Cart|null $cart
     *
     * @return array
     */
    private function gatherShippingAddress($data, \Webkul\Checkout\Models\Cart $cart): array
    {
        $customerAddress = [];

        if (isset($data['shipping']['address_id']) && $data['shipping']['address_id']) {
            $customerAddress = $this
                ->customerAddressRepository
                ->findOneWhere(['id' => $data['shipping']['address_id']])
                ->toArray();
        }

        $shippingAddress = array_merge(
            $customerAddress,
            $data['shipping'],
            ['cart_id' => $cart->id],
            $this->fillCustomerAttributes(),
            $this->fillAddressAttributes($data['shipping'])
        );

        return $shippingAddress;
    }

    /**
     * @param \Webkul\Checkout\Cart|null $cart
     * @param array                      $billingAddressData
     * @param array                      $shippingAddressData
     *
     * @throws \Prettus\Validator\Exceptions\ValidatorException
     */
    private function linkAddresses(
        \Webkul\Checkout\Models\Cart $cart,
        array $billingAddressData,
        array $shippingAddressData
    ): void
    {
        $billingAddressModel = $cart->billing_address;
        if ($billingAddressModel) {
            $billingAddressData['address_type'] = CartAddress::ADDRESS_TYPE_BILLING;
            $this->cartAddressRepository->update($billingAddressData, $billingAddressModel->id);

            if ($cart->haveStockableItems()) {
                $shippingAddressModel = $cart->shipping_address;
                if ($shippingAddressModel) {
                    if (isset($billingAddressData['use_for_shipping']) && $billingAddressData['use_for_shipping']) {
                        $billingAddressData['address_type'] = CartAddress::ADDRESS_TYPE_SHIPPING;
                        $this->cartAddressRepository->update($billingAddressData, $shippingAddressModel->id);
                    } else {
                        $shippingAddressData['address_type'] = CartAddress::ADDRESS_TYPE_SHIPPING;
                        $this->cartAddressRepository->update($shippingAddressData, $shippingAddressModel->id);
                    }
                } else {
                    if (isset($billingAddressData['use_for_shipping']) && $billingAddressData['use_for_shipping']) {
                        $this->cartAddressRepository->create(array_merge($billingAddressData,
                            ['address_type' => CartAddress::ADDRESS_TYPE_SHIPPING]));
                    } else {
                        $this->cartAddressRepository->create(array_merge($shippingAddressData,
                            ['address_type' => CartAddress::ADDRESS_TYPE_SHIPPING]));
                    }
                }
            }
        } else {
            $this->cartAddressRepository->create(array_merge($billingAddressData, ['address_type' => CartAddress::ADDRESS_TYPE_BILLING]));

            if ($cart->haveStockableItems()) {
                if (isset($billingAddressData['use_for_shipping']) && $billingAddressData['use_for_shipping']) {
                    $this->cartAddressRepository->create(array_merge($billingAddressData, ['address_type' => CartAddress::ADDRESS_TYPE_SHIPPING]));
                } else {
                    $this->cartAddressRepository->create(array_merge($shippingAddressData, ['address_type' => CartAddress::ADDRESS_TYPE_SHIPPING]));
                }
            }
        }
    }
}
