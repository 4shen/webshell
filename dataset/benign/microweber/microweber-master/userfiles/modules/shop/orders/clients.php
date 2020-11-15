<?php
only_admin_access();

?>
<script type="text/javascript">
    function mw_delete_shop_client($email) {
        var r = confirm("<?php _ejs("Are you sure you want to delete this client"); ?>?");
        if (r == true) {

            var r1 = confirm("<?php _ejs("ATTENTION"); ?>!!!!!!\n<?php _ejs("ALL ORDERS FROM THIS CLIENT WILL BE DELETED"); ?>!\n\n<?php _ejs("CLICK CANCEL NOW"); ?>\n<?php _ejs("OR"); ?>\n<?php _e("THERE IS NO TURNING BACK"); ?>!")
            if (r1 == true) {
                $.post("<?php print api_link('delete_client') ?>", {email: $email}, function (data) {
                    mw.reload_module('shop/orders/clients');
                });
            }
        }
    }

</script>

<script>

    $(window).bind('load', function () {


        mw.on.hashParam('clients_search', function () {


            var dis = this;
            if (dis !== '') {
                mw.$('#<?php print $params['id'] ?>').attr("data-keyword", dis);

            }
            else {
                mw.$('#<?php print $params['id'] ?>').removeAttr("data-keyword");
                mw.url.windowDeleteHashParam('clients_search')
            }
            mw.reload_module('#<?php print $params['id'] ?>');


        })

        mw.responsive.table('#shop-orders', {
            breakPoints: {
                768: 4,
                600: 2,
                400: 1
            }
        })
    });

</script>


<?php

$keyword = '';
$keyword_search = '';
if (isset($params['keyword'])) {
    $keyword = strip_tags($params['keyword']);
    $keyword_search = '&keyword=' . $keyword;
}


$clients = array();


$orders = get_orders('order_by=created_at desc&groupby=email&is_completed=1' . $keyword_search);


$is_orders = get_orders('count=1');

?>
<?php if (isset($params['keyword']) and $params['keyword'] != false): ?>
    <script>
        $(function () {

            $('[autofocus]').focus(function () {
                this.selectionStart = this.selectionEnd = this.value.length;
            });

            $('[autofocus]:not(:focus)').eq(0).focus();


        });
    </script>
<?php endif; ?>

<div class="section-header">
    <h2 class="pull-left"><span class="mw-icon-users"></span><?php _e("Clients List"); ?></h2>

    <input type="text" class="mw-ui-searchfield pull-right active" placeholder="<?php _e("Search in clients"); ?>"
           onkeyup="mw.on.stopWriting(this,function(){mw.url.windowHashParam('clients_search',this.value)})" value="<?php print $keyword ?>" autofocus="autofocus"/>
</div>

<div class="admin-side-content">
    <?php if ($is_orders != 0) { ?>
        <div class="table-responsive">
            <table class="mw-ui-table table-style-2 layout-auto table-clients" id="shop-orders" cellpadding="0" cellspacing="0" width="960">
                <thead>
                <tr>
                    <th><?php _e("Image"); ?></th>
                    <th><?php _e("Name"); ?></th>
                    <th><?php _e("Email"); ?></th>
                    <th><?php _e("Client's Phone"); ?></th>
                    <th><?php _e("Country"); ?></th>
                    <th><?php _e("City"); ?></th>
                    <th><?php _e("Orders #"); ?></th>
                    <th><?php _e("View & Delete"); ?></th>
                </tr>
                </thead>
                <tfoot>
                <tr>
                    <td><?php _e("Image"); ?></td>
                    <td><?php _e("Name"); ?></td>
                    <td><?php _e("Email"); ?></td>
                    <td><?php _e("Client's Phone"); ?></td>
                    <td><?php _e("Country"); ?></td>
                    <td><?php _e("City"); ?></td>
                    <td><?php _e("Orders #"); ?></td>
                    <td><?php _e("View & Delete"); ?></td>
                </tr>
                </tfoot>
                <tbody>
                <?php if (!empty($orders)): foreach ($orders as $order) : ?>
                    <tr>
                        <td class="text-center">
                            <a href="#?clientorder=<?php print $order['id']; ?>" class="text-center">
                                <?php if (user_picture($order['created_by'])): ?>
                                    <span class=" mw-user-thumb image" style="background-image: url('<?php echo user_picture($order['created_by']); ?>');">
                                </span>
                                <?php else: ?>
                                    <span class="mw-user-thumb  mai-user3"></span>
                                <?php endif; ?>
                            </a>
                        </td>
                        <td><a href="#?clientorder=<?php print $order['id']; ?>"><?php print $order['first_name'] . " " . $order['last_name']; ?></a></td>
                        <td><?php print $order['email']; ?></td>
                        <td><?php print $order['phone']; ?></td>
                        <td><?php print $order['country']; ?></td>
                        <td><?php print $order['city']; ?></td>
                        <td><?php $total_ord = get_orders('count=1&email=' . $order['email'] . '&is_completed=1'); ?>
                            <?php print $total_ord; ?></td>
                        <td width="115">

                            <a class="mw-ui-btn mw-ui-btn-info mw-ui-btn-outline mw-ui-btn-small" href="#?clientorder=<?php print $order['id']; ?>"><?php _e("View"); ?></a>

                            <span class="client-remove"><span class="mw-icon-bin" onclick="mw_delete_shop_client('<?php print ($order['email']) ?>');"></span>
                      </span>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    <?php } else { ?>
        <div class="mw-ui-box mw-ui-box-content info-box">
            <h2>
                <?php _e("You don't have any clients"); ?>
            </h2>
        </div>
    <?php } ?>
</div>
