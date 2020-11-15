<script type="text/javascript">
    mw.require('options.js');

    __shipping_options_save_msg = function () {
        if (mw.notification != undefined) {
            mw.notification.success('Shipping options are saved!');
        }
        mw.reload_module_parent('shop/shipping');

    }

    shippingToCountryClass = function (el) {
        var data = {
            option_group: 'shipping',
            option_key: 'shipping_gw_shop/shipping/gateways/country',
            option_value: el.checked ? 'y' : 'n'
        }
        mw.options.saveOption(data, function () {
            __shipping_options_save_msg()
        });
    }

    $(document).ready(function () {
        mw.options.form('.mw-set-shipping-options-swticher', __shipping_options_save_msg);
    });
</script>


<?php
$here = dirname(__FILE__) . DS . 'gateways' . DS;
// $shipping_modules = scan_for_modules("cache_group=modules/global/shipping&dir_name={$here}");
$shipping_modules = get_modules("type=shipping_gateway");
?>

<div>
    <?php if (is_array($shipping_modules)): ?>
        <?php foreach ($shipping_modules as $shipping_module): ?>
            <?php if (mw()->modules->is_installed($shipping_module['module'])): ?>


                <div class="mw-ui-row">
                    <div class="pull-left" id="set-shipping-to-country">
                        <?php $status = get_option('shipping_gw_' . $shipping_module['module'], 'shipping') == 'y' ? 'notification' : 'warn'; ?>

                        <span class="switcher-label-left enable-shipping-label"><?php _e("Enable shipping to countries"); ?></span>

                        <label class="mw-switch inline-switch pull-right">
                            <input
                                    onchange="shippingToCountryClass(this)"
                                    type="checkbox"
                                    name="shipping_gw_<?php print $shipping_module['module'] ?>"
                                    data-option-group="shipping"
                                    data-id="shipping_gw_<?php print $shipping_module['module'] ?>"
                                    data-value-checked="y"
                                    data-value-unchecked="n"
                                    class="mw_option_field"
                                <?php if ($status == 'notification'): ?> checked  <?php endif; ?>>
                            <span class="mw-switch-off">OFF</span>
                            <span class="mw-switch-on">ON</span>
                            <span class="mw-switcher"></span>
                        </label>
                    </div>

                    <div class="pull-right buttons-holder">
                        <a href="javascript:;"
                           class="mw-ui-btn mw-ui-btn-normal mw-ui-btn-info mw-ui-btn-outline pull-right"
                           onclick="mw.tools.open_global_module_settings_modal('shop/shipping/set_units', 'shipping');">
                            <span class="mw-icon-gear"></span><?php _e("Set shipping units"); ?>
                        </a>

                        <a class="mw-ui-btn mw-ui-btn-normal mw-ui-btn-info pull-right m-r-10"
                           href="javascript:mw_admin_edit_country_item_popup();"
                           xxxonclick="mw.$('.add-new-country').show();$('.add-new-country').find('.hide-item').toggleClass('hidden');">
                            <span class="mw-icon-plus"></span> <?php _e("Add Country"); ?>
                        </a>
                        <div class="clearfix"></div>
                    </div>

                </div>

            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

 

<div class="mw-set-shipping-options mw-admin-wrap">
    <div class="">
        <?php if (is_array($shipping_modules)): ?>
            <?php foreach ($shipping_modules as $shipping_module): ?>
                <?php if (mw()->modules->is_installed($shipping_module['module'])): ?>


                    <div class="mw-set-shipping-gw-options">
                        <module type="<?php print $shipping_module['module'] ?>" view="admin"/>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
