<!DOCTYPE html>
<html <?php print lang_attributes(); ?>>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="robots" content="noindex">
    <script type="text/javascript">
        if (!window.CanvasRenderingContext2D) {
            var h = "<div id='UnsupportedBrowserMSG'><h1><?php _e("Your a need better browser to run Microweber>"); ?></h1></div>"
                + "<div id='download_browsers_holder'><h2><?php _e("Update your browser"); ?></h2><p id='choose_browsers'>"
                + "<a id='u__ie' target='_blank' href='http://windows.microsoft.com/en-us/internet-explorer/download-ie'></a>"
                + "<a id='u__ff' target='_blank' href='http://www.mozilla.org/en-US/firefox/new/'></a>"
                + "<a id='u__chr' target='_blank' href='https://www.google.com/intl/en/chrome/'></a>"
                + "<a id='u__sf' target='_blank' href='http://support.apple.com/kb/DL1531'></a>"
                + "</p></div>";
            document.write(h);
            document.body.id = 'UnsupportedBrowser';
            document.body.className = 'UnsupportedBrowser';
        }
        mwAdmin = true;
        admin_url = '<?php print admin_url(); ?>';
    </script>
    <script type="text/javascript">
        mw.lib.require('jqueryui');
    </script>
    <script type="text/javascript">

        mw.require("<?php print mw_includes_url(); ?>api/libs/jquery_slimscroll/jquery.slimscroll.min.js");
        mw.require("liveadmin.js");
        mw.require("<?php print mw_includes_url(); ?>css/wysiwyg.css");
        mw.require("<?php print mw_includes_url(); ?>css/components.css");
        mw.require("<?php print mw_includes_url(); ?>css/admin.css");
        mw.require("<?php print mw_includes_url(); ?>css/admin-new.css");
        mw.require("wysiwyg.js");
        mw.require("url.js");
        mw.require("options.js");
        mw.require("events.js");
        mw.require("admin.js");
        mw.require("editor_externals.js");
        mw.require("keys.js");
        mw.require("css_parser.js");
        mw.require("custom_fields.js");
        mw.require("session.js");
        mw.require("content.js");
        mw.require("upgrades.js");
        mw.require("tree.js");

        mw.lib.require('font_awesome5');

        <?php /*  mw.require("<?php print mw_includes_url(); ?>css/helpinfo.css");
        mw.require("helpinfo.js");*/ ?>
        <?php if(_lang_is_rtl()){ ?>
        mw.require("<?php print mw_includes_url(); ?>css/rtl.css");
        <?php } ?>
    </script>
    <?php if (!isset($_REQUEST['no_toolbar'])): ?>
        <script type="text/javascript">
            $(document).ready(function () {

                $('.mw-lazy-load-module').reload_module();

                if (self === top) {
                    window.onhashchange = function () {
                        mw.cookie.set('back_to_admin', window.location.href);
                    }
                    mw.cookie.set('back_to_admin', window.location.href);
                }
                mw.$("#mw-quick-content,#mw_edit_pages_content,#mw-admin-content").click(function () {
                    if (mw.helpinfo != undefined) {
                        mw.cookie.set('helpinfo', false, 4380);
                        $(".helpinfo_helper").fadeOut();
                    }
                });
            });
            // mw.require("<?php print mw_includes_url(); ?>css/ui.css");
            mw.require("fonts.js");


            $(window).load(function () {
                // test = new mw.font();
                // test.add({
                //     family: {
                //         'Roboto': [300, 500],
                //         'Tajawal': [400, 700]
                //     },
                //     subset: ["cyrillic", "cyrillic-ext", "korean", "latin-ext"]
                // });
                // test.add({
                //     family: {
                //         'Roboto': [900, 55],
                //         'Tajawal': [1, 2],
                //     }
                // });
                if ($(".bootstrap3ns").size() > 0) {
                    mw.lib.require("bootstrap3ns");
                }
            });


        </script>
    <?php endif; ?>
    <?php event_trigger('admin_head'); ?>
</head>
<body class="is_admin loading view-<?php print mw()->url_manager->param('view'); ?> action-<?php print mw()->url_manager->param('action'); ?>">


<?php

$new_version_notifications = mw()->notifications_manager->get('rel_type=update_check&rel_id=updates');

?>


<?php
$past_page = site_url() . '?editmode=y';
$last_page_front = session_get('last_content_id');
if ($last_page_front == false) {
    if (isset($_COOKIE['last_page'])) {
        $last_page_front = $_COOKIE['last_page'];
    }
}

if ($last_page_front != false) {
    $cont_by_url = mw()->content_manager->get_by_id($last_page_front, true);
    if (isset($cont_by_url) and $cont_by_url == false) {
        $past_page = mw()->content_manager->get("order_by=updated_at desc&limit=1");
        $past_page = mw()->content_manager->link($past_page[0]['id']);
    } else {
        $past_page = mw()->content_manager->link($last_page_front);
    }
} else {
    $past_page = mw()->content_manager->get("order_by=updated_at desc&limit=1");
    if (isset($past_page[0])) {
        $past_page = mw()->content_manager->link($past_page[0]['id']);
    }
}


?>
<?php
$last_page_front = session_get('last_content_id');
if ($last_page_front == false) {
    if (isset($_COOKIE['last_page'])) {
        $last_page_front = $_COOKIE['last_page'];
    }
}
if ($last_page_front != false) {
    $cont_by_url = mw()->content_manager->get_by_id($last_page_front, true);
    if (isset($cont_by_url) and $cont_by_url == false) {
        $past_page = mw()->content_manager->get("order_by=updated_at desc&limit=1");
        $past_page = mw()->content_manager->link($past_page[0]['id']);
    } else {
        $past_page = mw()->content_manager->link($last_page_front);
    }
} else {
    $past_page = mw()->content_manager->get("order_by=updated_at desc&limit=1");
    if (isset($past_page[0])) {
        $past_page = mw()->content_manager->link($past_page[0]['id']);

    } else {
        $past_page = site_url();
    }
}


$shop_disabled = get_option('shop_disabled', 'website') == 'y';

if (!$shop_disabled) {
    if (!mw()->modules->is_installed('shop')) {
        $shop_disabled = true;
    }
}


?>
<?php /*<div id="admin-user-nav">


<a href="javascript:;" class="mw-icon-off pull-right"></a>
<a href="<?php print $past_page; ?>?editmode=y" class="mw-ui-btn mw-ui-btn-invert pull-right"><span class="mw-icon-live"></span><?php _e("Live Edit"); ?></a>

</div>*/ ?>


<script>
    $(document).ready(function () {
        $(".mw-admin-mobile-admin-sidebar-toggle").on('click', function () {
            $("#main-bar").toggleClass('mobile-active')
        })
        $("body").on('click', function (e) {
            if (!mw.tools.hasAnyOfClassesOnNodeOrParent(e.target, ['mw-admin-mobile-admin-sidebar-toggle'])) {
                $("#main-bar").removeClass('mobile-active')
            }

        })


    })


    function mw_admin_add_order_popup(ord_id) {

        if (!!ord_id) {
            var modalTitle = '<?php _e('Edit order'); ?>';
        } else {
            var modalTitle = '<?php _e('Add order'); ?>';
        }


        mw_admin_edit_order_item_popup_modal_opened = mw.modal({
            content: '<div id="mw_admin_edit_order_item_module"></div>',
            title: modalTitle,
            id: 'mw_admin_edit_order_item_popup_modal',
            width: 900,
            height: 800
        });

        var params = {}
        params.order_id = ord_id;
        mw.load_module('shop/orders/admin/add_order', '#mw_admin_edit_order_item_module', null, params);
    }


</script>

<?php if (is_admin()): ?>

    <?php

    $order_notif_html = false;
    $new_orders_count = mw()->order_manager->get_count_of_new_orders();
    if ($new_orders_count) {
        $order_notif_html = '<sup class="mw-notification-count">' . $new_orders_count . '</sup>';
    }

    ?>
    <?php

    $comments_notif_html = false;
    $new_comments_count = mw()->notifications_manager->get('module=comments&is_read=0&count=1');
    if ($new_comments_count) {
        $comments_notif_html = '<sup class="mw-notification-count">' . $new_comments_count . '</sup>';

    }

    ?>


    <?php
    $notif_html = '';

    $notif_count = mw()->notifications_manager->get_unread_count();

    if ($notif_count > 0) {
        $notif_html = '<sup class="mw-notification-count">' . $notif_count . '</sup>';
    }


    ?>

    <div id="mw-admin-mobile-header">
        <nav>
            <a class="mw-admin-mobile-admin-sidebar-toggle"><span class="mw-icon-menu"></span></a>
            <a class="create-content-btn" data-tip="bottom-left"><span class="mw-icon-plus-circled"></span></a>
            <a class="mamh-shop" href="<?php print admin_url(); ?>view:shop/action:orders"><span
                        class="mai-shop"></span><?php print $notif_html; ?></a>
        </nav>
        <div id="user-menu-top" class="pull-right">
            <?php $user_id = user_id();
            $user = get_user_by_id($user_id);
            if (!empty($user)) {
                $img = user_picture($user_id);
                if ($img != '') {
                    ?>
                    <a href="javascript:;" id="main-bar-user-menu-link-top" class="main-bar-user-menu-link-has-image">
                        <span class="main-bar-profile-img"
                              style="background-image: url('<?php print $img; ?>');"></span>
                    </a>
                <?php } else { ?>
                    <a href="javascript:;" id="main-bar-user-menu-link-top" class="main-bar-user-menu-link-no-image">
                        <span class="mw-icon-user" id="main-bar-profile-icon"></span>
                    </a>
                <?php }
            } ?>

            <div id="user-menu-top-links" style="display: none">
                <div class="mw-ui-btn-vertical-nav main-bar-user-tip-navigation">
                    <a href="<?php print admin_url('view:modules/load_module:users#edit-user=' . $user_id); ?>"
                       class="mw-ui-btn">
                        <?php _e("My Profile"); ?>
                    </a>
                    <a href="<?php print admin_url('view:modules/load_module:users'); ?>" target="_blank"
                       class="mw-ui-btn">
                        <?php _e("Manage Users"); ?>

                    </a>

                    <?php if (mw()->ui->enable_service_links): ?>
                        <?php if (mw()->ui->custom_support_url): ?>
                            <a href="<?php print mw()->ui->custom_support_url ?>" class="mw-ui-btn">
                                <?php _e("Support"); ?>
                            </a>
                        <?php else: ?>
                            <a href="javascript:;" onmousedown="mw.contactForm();" class="mw-ui-btn">
                                <?php _e("Support"); ?>
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>

                    <a href="<?php print site_url(); ?>?editmode=y" class="mw-ui-btn go-live-edit-href-set">
                        <?php _e("View Website"); ?>
                    </a> <a href="<?php print api_url('logout'); ?>" class="mw-ui-btn">
                        <?php _e("Log out"); ?>
                    </a></div>
            </div>
        </div>

        <a href="<?php print $past_page ?>?editmode=y"
           class="mw-ui-btn mw-ui-btn-info mw-ui-btn-small pull-right toolbar-live-edit mobile-live-edit-btn"><i
                    class="mai-eye2"></i></a>
    </div>
<?php endif; ?>

<div id="mw-admin-container">
    <?php if (is_admin()): ?>
    <div class="admin-toolbar">
        <div class="create-content scroll-height-exception">
            <div class="mw-ui-row header-top-center-notifs">
                <div class="mw-ui-col">
                    <a href="javascript:;" class="mw-ui-btn create-content-btn" id="create-content-btn">
                        <span class="mai-plus"></span>
                        <?php _e("Add New"); ?>
                        <span class="mai-cd"></span>
                    </a>
                </div>
                <div class="mw-ui-col center">
                    <?php if ($new_orders_count != ''): ?>
                        <a href="<?php print admin_url(); ?>view:shop/action:orders"
                           class="mw-ui-btn mw-ui-btn-default notif-btn">
                            <span class="mai-shop"></span> &nbsp; <?php print $order_notif_html; ?>
                            <span class="notif-label">
                                <?php if ($new_orders_count == 1): ?>
                                    <?php _e("New order"); ?>
                                <?php elseif ($new_orders_count > 1): ?>
                                    <?php _e("New orders"); ?>
                                <?php endif; ?>
                            </span>
                        </a>
                    <?php endif; ?>

                    <?php if ($comments_notif_html != ''): ?>
                        <a href="<?php print admin_url(); ?>view:modules/load_module:comments"
                           class="mw-ui-btn mw-ui-btn-default notif-btn">
                            <span class="mai-comment"></span> &nbsp; <?php print $comments_notif_html; ?>
                            <span class="notif-label">
                                <?php if ($new_comments_count == 1): ?>
                                    <?php _e("New comment"); ?>
                                <?php elseif ($new_comments_count > 1): ?>
                                    <?php _e("New comments"); ?>
                                <?php endif; ?>
                            </span>
                        </a>
                    <?php endif; ?>

                    <?php if ($notif_count != ''): ?>
                        <a href="<?php print admin_url(); ?>view:admin__notifications"
                           class="mw-ui-btn mw-ui-btn-default notif-btn">
                            <span class="mai-notification"></span> &nbsp; <?php print $notif_html; ?>
                            <span class="notif-label">
                                <?php if ($notif_count == 1): ?>
                                    <?php _e("New notification"); ?>
                                <?php elseif ($notif_count > 1): ?>
                                    <?php _e("New notifications"); ?>
                                <?php endif; ?>
                            </span>
                        </a>
                    <?php endif; ?>
                </div>

                <?php
                event_trigger('mw.admin.header.toolbar');
                ?>

                <div class="mw-ui-col">

                    <a href="<?php print $past_page ?>?editmode=y"
                       class="mw-admin-go-live-now-btn mw-ui-btn mw-ui-btn-info toolbar-live-edit" target="_blank">
                        <span class="mai-eye2"></span> <?php _e("Live Edit"); ?>
                    </a>

                    <button type="submit" class="mw-ui-btn mw-ui-btn-notification btn-save tip pull-right js-top-save"
                            style="display: none;" data-tip="<?php _e("Save"); ?>" data-tipposition="bottom-center"
                            form="quickform-edit-content"><i class="fa fa-save"></i>
                        <span>&nbsp; <?php print _e('Save'); ?></span></button>
                </div>
            </div>
        </div>
    </div>


    <div class="mw-ui-row main-admin-row">
        <div class="mw-ui-col main-bar-column">
            <div id="main-bar" class="scroll-height-exception-master">
                <?php $view = url_param('view'); ?>
                <?php $action = url_param('action'); ?>
                <?php $load_module = url_param('load_module'); ?>
                <a href="<?php print site_url(); ?>?editmode=n" id="main-bar-mw-icon" target="_blank"
                   class="scroll-height-exception <?php if ($view == 'dashboard' or (url_current() == admin_url()) or url_current() == rtrim(admin_url(), '/')) {
                       print 'active';
                   } ?>">
                    <?php if (mw()->ui->admin_logo != false) : ?>
                        <img src="<?php print mw()->ui->admin_logo ?>" style="max-width: 100%; max-height: 36px;"/>
                    <?php else: ?>
                        <!--                        <span class="mai-logo"></span>-->
                        <!--                        <span class="mw-icon-microweber"></span>-->
                        <img src="<?php print mw()->ui->admin_logo_login(); ?>" alt="Microweber" class="admin-logo"/>
                    <?php endif; ?>
                    <strong><?php //print str_replace(array('http://','https://'), '', site_url()); ?></strong>
                </a>
                <ul id="mw-admin-main-menu">
                    <li <?php if (!$view): ?> class="active" <?php endif; ?>>
                        <a href="<?php print admin_url(); ?>">
                            <span class="mai-dashboard"></span>
                            <strong><?php _e("Dashboard"); ?></strong>
                        </a>
                    </li>


                    <li><?php event_trigger('mw.admin.sidebar.li.first'); ?></li>


                    <li
                        <?php if ($view == 'content' and $action == false): ?>
                            class="active"
                        <?php elseif ($view == 'content' and $action != false): ?>
                            class="active-parent"
                        <?php endif; ?>
                    >
                        <a href="<?php print admin_url(); ?>view:content" title="">
                            <span class="mai-website"></span>
                            <strong>
                                <?php _e("Website"); ?>
                            </strong> </a>
                        <ul>
                            <li <?php if ($action == 'pages'): ?> class="active" <?php endif; ?>>
                                <a href="<?php print admin_url(); ?>view:content/action:pages">
                                    <span class="mai-page"></span>
                                    <strong><?php _e("Pages"); ?></strong>
                                    <span class="mw-admin-main-menu-mini tip" data-tip="<?php _e("Add new page") ?>"
                                          data-href="<?php print admin_url('view:content#action=new:page'); ?>"><?php _e("Add"); ?></span>
                                </a></li>
                            <li <?php if ($action == 'posts'): ?> class="active" <?php endif; ?>>
                                <a href="<?php print admin_url(); ?>view:content/action:posts">
                                    <span class="mai-post"></span>
                                    <strong><?php _e("Posts"); ?></strong>
                                    <span class="mw-admin-main-menu-mini tip" data-tip="<?php _e("Add new post") ?>"
                                          data-href="<?php print admin_url('view:content#action=new:post'); ?>"><?php _e("Add"); ?></span>
                                </a></li>
                            <?php if ($shop_disabled == false AND mw()->modules->is_installed('shop') == true): ?>
                                <li <?php if ($action == 'products'): ?> class="active" <?php endif; ?>>
                                    <a href="<?php print admin_url(); ?>view:content/action:products">
                                        <span class="mai-product"></span>
                                        <strong><?php _e("Products"); ?></strong>
                                        <span class="mw-admin-main-menu-mini tip"
                                              data-tip="<?php _e("Add new product") ?>"
                                              data-href="<?php print admin_url('view:content#action=new:product'); ?>"><?php _e("Add"); ?></span>
                                    </a></li>
                            <?php endif; ?>
                            <li <?php if ($action == 'categories'): ?> class="active" <?php endif; ?>>
                                <a href="<?php print admin_url(); ?>view:content/action:categories">
                                    <span class="mai-category"></span>
                                    <strong> <?php _e("Categories"); ?></strong>
                                    <span class="mw-admin-main-menu-mini tip" data-tip="<?php _e("Add new category") ?>"
                                          data-href="<?php print admin_url('view:content#action=new:category'); ?>"><?php _e("Add"); ?></span>
                                </a></li>
                        </ul>
                    </li>

                    <?php if ($shop_disabled == false AND mw()->modules->is_installed('shop') == true): ?>

                        <li
                            <?php if ($view == 'shop' and $action == false): ?> class="active"
                            <?php elseif ($view == 'shop' and $action != false): ?> class="active-parent"
                            <?php elseif ($view == 'modules' and $load_module == 'shop__coupons'): ?> class="active"
                            <?php endif; ?> >
                            <a href="<?php print admin_url(); ?>view:shop" title="">
                                <span class="mai-market2"></span>
                                <strong><?php _e("Shop"); ?></strong>
                                <?php if ($view != 'shop' and $notif_count > 0) {
                                    print $order_notif_html;
                                }; ?>
                            </a>
                            <ul>


                                <li <?php if ($action == 'products'): ?> class="active" <?php endif; ?>>
                                    <a href="<?php print admin_url(); ?>view:shop/action:products">
                                        <span class="mai-product"></span>
                                        <?php _e("Products"); ?>
                                    </a>
                                </li>

                                <li <?php if ($action == 'orders'): ?> class="active" <?php endif; ?>>
                                    <a href="<?php print admin_url(); ?>view:shop/action:orders">
                                        <span class="mai-shop"></span>
                                        <?php _e("Orders"); ?>
                                        <?php if ($view == 'shop') {
                                            print $order_notif_html;
                                        } ?>

                                    </a>
                                </li>


                                <li <?php if ($action == 'clients'): ?> class="active" <?php endif; ?>>
                                    <a href="<?php print admin_url(); ?>view:shop/action:clients">
                                        <span class="mai-user"></span>
                                        <?php _e("Clients"); ?>
                                    </a>
                                </li>


                                <li <?php if ($action == 'options'): ?> class="active" <?php endif; ?>>

                                    <a href="<?php print admin_url(); ?>view:shop/action:options/">
                                        <span class="mw-icon-web-settings" style="margin-top: 16px;"></span>
                                        <?php _e("Shop settings"); ?>
                                    </a>
                                </li>
                                <?php
                                /*<li>
                                    <a href="<?php print admin_url(); ?>view:shop/action:options/#?option=payment-methods">
                                        <span class="mai-order"></span>
                                        <?php _e("Payment methods"); ?>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php print admin_url(); ?>view:shop/action:options/#?option=taxes">
                                        <span class="mai-percent"></span>
                                        <?php _e("Taxes"); ?>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php print admin_url(); ?>view:shop/action:options/#?option=shipping">
                                        <span class="mai-shipping"></span>
                                        <?php _e("Shipping"); ?>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php print admin_url(); ?>view:shop/action:options/#?option=email">
                                        <span class="mai-mail"></span>
                                        <?php _e("Email confirmation"); ?>
                                    </a>
                                </li>


                                <li <?php if ($action == 'options'): ?> class="active" <?php endif; ?>>

                                    <a href="<?php print admin_url(); ?>view:shop/action:options/#?option=other">
                                        <span class="mai-options"></span>
                                        <?php _e("Options"); ?>
                                    </a>
                                </li>*/


                                ?>


                            </ul>
                        </li>
                    <?php endif; ?>


                    <li <?php if (
                    ($view == 'modules' AND $load_module != 'users' AND $load_module != 'shop__coupons')
                    ): ?> class="active" <?php endif; ?>><a class="item-admin__modules"
                                                            href="<?php print admin_url(); ?>view:modules">
                            <span class="mai-modules"></span><strong><?php _e("Modules"); ?></strong>
                        </a>


                        <ul class="mw-ui-sidenav">
                            <!--
                            <li <?php /*if (
                                $view == 'modules' AND $load_module != 'users' AND $load_module != 'shop__coupons'
                            ): */ ?> class="active" <?php /*endif; */ ?>  ><a class="item-admin__modules" href="<?php /*print admin_url(); */ ?>view:modules">
                                    <span class="mai-modules"></span><strong><?php /*_e("My Modules"); */ ?></strong>
                                </a>
                            </li>
-->


                            <?php if (mw()->ui->disable_marketplace != true): ?>
                                <!--   <li <?php /*if ($view == 'packages'): */ ?> class="active" <?php /*endif; */ ?>>
                                    <a href="<?php /*print admin_url(); */ ?>view:packages">
                                        <span class="mai-market"></span> <strong>
                                            <?php /*_e("Marketplace"); */ ?>
                                        </strong>
                                    </a>
                                </li>-->

                            <?php endif; ?>


                        </ul>

                    </li>


                    <!-- NE BARAI ROKER! -->
                    <?php if (mw()->ui->disable_marketplace != true): ?>
                        <li class="mw-admin-menu-sidebar-marketplace-desktop <?php if ($view == 'packages'): ?>active<?php endif; ?>">
                            <a href="<?php print admin_url(); ?>view:packages"><i
                                        class="mai-market"></i><strong><?php _e("Marketplace"); ?></strong></a>
                        </li>
                    <?php endif; ?>
                    <!-- NE BARAI ROKER! -->

                    <li <?php if (!url_param('has_core_update') and ($view == 'settings') /*or ($load_module AND $load_module != 'users')*/): ?> class="active" <?php endif; ?>>
                        <a href="<?php print admin_url(); ?>view:settings#option_group=website"> <span
                                    class="mai-setting"></span>
                            <strong>
                                <?php _e("Settings"); ?>

                            </strong>
                        </a>


                        <ul class="mw-ui-sidenav">
                            <li><a class="item-website"
                                   href="<?php print admin_url(); ?>view:settings#option_group=website">
                                    <span class="mai-website"></span><strong><?php _e("Website"); ?></strong>
                                </a>
                            </li>
                            <li><a class="item-template"
                                   href="<?php print admin_url(); ?>view:settings#option_group=template">
                                    <span class="mai-templates"></span><strong><?php _e("Template"); ?></strong>
                                </a>
                            </li>

                            <li><a class="item-users"
                                   href="<?php print admin_url(); ?>view:settings#option_group=users">
                                    <span class="mai-login"></span><strong><?php _e("Login & Register"); ?></strong>
                                </a>
                            </li>
                            <li><a class="item-email"
                                   href="<?php print admin_url(); ?>view:settings#option_group=email">
                                    <span class="mai-mail"></span><strong><?php _e("Email"); ?></strong>
                                </a>
                            </li>


                            <?php event_trigger('mw_admin_settings_menu'); ?>
                            <?php $settings_menu = mw()->modules->ui('admin.settings.menu'); ?>
                            <?php if (is_array($settings_menu) and !empty($settings_menu)): ?>
                                <?php foreach ($settings_menu as $item): ?>
                                    <?php $module = (isset($item['module'])) ? module_name_encode($item['module']) : false; ?>
                                    <?php $title = (isset($item['title'])) ? ($item['title']) : false; ?>
                                    <?php $class = (isset($item['class'])) ? ($item['class']) : false; ?>
                                    <?php if ($module != 'admin') { ?>
                                        <li>
                                            <a onclick="mw.url.windowHashParam('option_group', '<?php print $module ?>');return false;"
                                               class="<?php print $class ?>"
                                               href="#option_group=<?php print $module ?>">
                                                <span class="<?php print isset($item['icon']) ? $item['icon'] : ''; ?>"></span>
                                                <strong><?php print $title ?></strong>
                                            </a>
                                        </li>
                                    <?php } ?>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <?php
                            /*
                            <?php $got_lic = mw()->update->get_licenses('count=1') ?>
                            <?php if (($got_lic) >= 0): ?>
                                <li>
                                    <a onclick="mw.url.windowHashParam('option_group', 'licenses');return false;" class="item-licenses" href="#option_group=licenses">
                                        <span class="mai-licenses"></span>
                                        <strong><?php _e("Licenses"); ?></strong>
                                    </a>
                                </li>
                            <?php endif; ?>


                            */

                            ?>

                            <li>
                                <a onclick="mw.url.windowHashParam('option_group', 'advanced');return false;"
                                   class="item-advanced" href="#option_group=advanced">
                                    <span class="mai-options"></span>
                                    <stong><?php _e("Advanced"); ?></stong>
                                </a></li>
                            <li><a onclick="mw.url.windowHashParam('option_group', 'language');return false;"
                                   class="item-language" href="#option_group=language">
                                    <span class="mai-languages"></span>
                                    <strong><?php _e("Language"); ?></strong>
                                </a></li>
                        </ul>

                    </li>
                    <?php $load_module = url_param('load_module'); ?>
                    <li <?php print 'class="' . ($load_module == 'users' ? 'active' : '') . '"'; ?>>
                        <a href="<?php print admin_url('view:modules/load_module:users'); ?>"
                           id="main-bar-user-menu-link" class="main-bar-user-menu-link-no-image">
                            <span class="mai-user2"></span>
                            <strong><?php _e("Users"); ?></strong>
                        </a>
                        <ul>
                            <li>
                                <a href="<?php print admin_url('view:modules/load_module:users#edit-user=' . $user_id); ?>"
                                   id="main-menu-my-profile"><?php _e("My Profile"); ?></a></li>
                            <li><a href="<?php print admin_url('view:modules/load_module:users'); ?>"
                                   id="main-menu-manage-users"><?php _e("Manage Users"); ?></a></li>

                            <?php if (mw()->ui->enable_service_links): ?>
                                <?php if (mw()->ui->custom_support_url): ?>
                                    <li>
                                        <a href="<?php print mw()->ui->custom_support_url ?>"><strong><?php _e("Support"); ?></strong></a>
                                    </li>
                                <?php else: ?>
                                    <li><a href="javascript:;"
                                           onmousedown="mw.contactForm();"><strong><?php _e("Support"); ?></strong></a>
                                    </li>
                                <?php endif; ?>
                            <?php endif; ?>

                            <li><a href="<?php print site_url(); ?>?editmode=y"
                                   class=go-live-edit-href-set"><?php _e("View Website"); ?></a></li>


                            <li class="mw-admin-menu-sidebar-logout-mobile"><a href="<?php print api_url('logout'); ?>"><span
                                            class="mai-login"></span><strong><?php _e("Log out"); ?></strong></a></li>
                        </ul>
                    </li>

                    <li><?php event_trigger('mw.admin.sidebar.li.last'); ?></li>


                    <li class="mw-admin-menu-sidebar-logout-desktop"><a href="<?php print api_url('logout'); ?>"><i
                                    class="mw-icon-off"></i><strong><?php _e("Log out"); ?></strong></a></li>

                    <li id="mw-admin-main-menu-toggle"><a href="javascript:;"><span class="mw-icon-menu"></span></a>
                    </li>
                </ul>

                <script>
                    $(document).ready(function () {
                        $('.go-live-edit-href-set').bind('mousedown', function () {
                            var url_to_go = $(this).attr('href');

                            var n = url_to_go.indexOf("editmode");
                            if (n == -1) {
                                url_to_go = url_to_go + '?editmode:y';
                            }

                            $(this).attr('href', url_to_go);
                            return false;
                        });
                    });
                </script>
            </div>
            <span id="mb-active"></span>
        </div>
        <?php endif; ?>
