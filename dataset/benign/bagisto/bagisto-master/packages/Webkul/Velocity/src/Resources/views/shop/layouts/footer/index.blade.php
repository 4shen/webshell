<div class="footer">
    <div class="footer-content">

        @include('shop::layouts.footer.newsletter-subscription')
        @include('shop::layouts.footer.footer-links')

        {{-- @if ($categories)
            @include('shop::layouts.footer.top-brands')
        @endif --}}

        @if (core()->getConfigData('general.content.footer.footer_toggle'))
            @include('shop::layouts.footer.copy-right')
        @endif
    </div>
</div>


