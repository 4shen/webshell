<div class="mini-cart-container pull-right">
    <mini-cart
        view-cart="{{ route('shop.checkout.cart.index') }}"
        cart-text="{{ __('shop::app.minicart.view-cart') }}"
        checkout-text="{{ __('shop::app.minicart.checkout') }}"
        checkout-url="{{ route('shop.checkout.onepage.index') }}"
        subtotal-text="{{ __('shop::app.checkout.cart.cart-subtotal') }}">
    </mini-cart>
</div>