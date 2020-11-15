<?php
only_admin_access();
$is_shop = false;

if (isset($params['is_shop'])) {
    $is_shop = $params['is_shop'];
}

$dir_name = normalize_path(modules_path());

$posts_mod = $dir_name . 'content' . DS . 'admin_live_edit_tab1.php';
?>
<?php
$set_content_type_mod = 'page';
if (isset($params['global']) and $params['global'] != false) {
    $set_content_type_mod_1 = get_option('data-content-type', $params['id']);
    if ($set_content_type_mod_1 != false and $set_content_type_mod_1 != '') {
        $set_content_type_mod = $set_content_type_mod_1;
    }
}

$add_post_q = '';

if (isset($params['id'])) {
    $add_post_q .= ' module-id="' . $params['id'] . '" ';

}

if (isset($params['content-id'])) {
    $add_post_q .= ' data-content-id=' . $params['content-id'];
}
if (isset($params['related'])) {
    $add_post_q .= ' related=' . $params['related'];
}
$parent_page = false;
$is_global = false;
if (isset($params['global'])) {
    $add_post_q .= ' global=' . $params['global'];
    $is_global = true;
} else {
    $set_content_type = get_option('data-content-type', $params['id']);

    if ($set_content_type == 'page') {
        $add_post_q .= ' global="true" ';
        $is_global = true;

    }
}
if ($is_global == false) {
    if (isset($params['is_shop']) and ($params['is_shop'] == 'y' or $params['is_shop'] == 1)) {
        $add_post_q .= ' content_type="product"   ';
    } else if (isset($params['content_type']) and $params['content_type'] != '') {
        $add_post_q .= ' content_type="' . $params['content_type'] . '"   ';
    } else {
        $add_post_q .= ' content_type="post" ';
    }
}

$posts_parent_page = get_option('data-content-id', $params['id']);
$posts_parent_category = get_option('data-category-id', $params['id']);
if ($posts_parent_page == false) {
    $posts_parent_page_id = intval(get_option('data-page-id', $params['id']));
    if ($posts_parent_page_id != 0) {
        $add_post_q .= ' parent-page-id=' . intval($posts_parent_page_id);
    }

}

if ($posts_parent_page != false and intval($posts_parent_page) > 0) {
    $add_post_q .= ' data-content-id=' . intval($posts_parent_page);
    $parent_page = $posts_parent_page;
} else if (isset($params['content-id'])) {
    $add_post_q .= ' data-content-id=' . $params['content-id'];
    $parent_page = $params['content-id'];

}

if ($posts_parent_category == false) {
    if (isset($params['category'])) {
        $posts_parent_category = $params['category'];


    }

}

if ($posts_parent_category != false) {
    $add_post_q .= ' parent-category-id="' . intval($posts_parent_category) . '" ';
}

if (!isset($params['global']) and $posts_parent_page != false and $posts_parent_category != false and intval($posts_parent_category) > 0) {

    $str0 = 'table=categories&limit=1000&data_type=category&what=categories&' . 'parent_id=0&rel_id=' . $posts_parent_page;
    $page_categories = db_get($str0);
    $sub_cats = array();
    $page_categories = db_get($str0);
    if (is_array($page_categories)) {
        foreach ($page_categories as $item_cat) {
            $sub_cats[] = $item_cat['id'];
            $more = get_category_children($item_cat['id']);
            if ($more != false and is_array($more)) {
                foreach ($more as $item_more_subcat) {
                    $sub_cats[] = $item_more_subcat;
                }
            }

        }
    }

    if (is_array($sub_cats) and in_array($posts_parent_category, $sub_cats)) {
        $add_post_q .= ' selected-category-id=' . intval($posts_parent_category);
    }

}

if (isset($params['is_shop']) and $params['is_shop'] == 'y') {
    $add_post_q .= ' content_type="product"   ';
} else {
    $add_post_q .= '  ';
}

?>
<style>
    .mw-ui-box.tab {
        display: none;
    }


    .manage-posts-holder, .manage-post-item {
        border: none
    }

    .manage-toobar-content {
        margin-bottom: 10px;
    }
    .post-settings-holder .mw-ui-btn-nav{
        clear: none;
    }
</style>
<script type="text/javascript">

    //    $( window ).bind( "adminSaveContentCompleted", function() {
    //
    //
    //        alert( "Content is saved "  );
    //    });


    resizeModal = function (w, h) {
        if(window.thismodal && thismodal.resize){
            thismodal.resize(w, h);
            thismodal.center();
        }

    };


    mw.on.hashParam("action", function () {
        var id = (this.split(':')[1]);


        if (this == 'new:post' || this == 'new:page' || this == 'new:product') {
            mw.add_new_content_live_edit(id);
        } else if (this == 'editpage') {
            // $('#mw_posts_create_live_edit').html("Content is added");
        } else {
            mw.edit_content_live_edit(id);
        }

    });

    mw.add_new_content_live_edit = function ($cont_type) {


        Tabs.set(3);

        $('#mw_posts_create_live_edit').removeAttr('data-content-id');
        $('#mw_posts_create_live_edit').attr('from_live_edit', 1);
        if ($cont_type == undefined) {
            $('#mw_posts_create_live_edit').removeAttr('content_type');

        } else {
            if ($cont_type == 'page') {
                $('#mw_posts_create_live_edit').removeAttr('subtype');
            } else {
                $('#mw_posts_create_live_edit').attr('subtype', $cont_type);

            }
            $('#mw_posts_create_live_edit').attr('content_type', $cont_type);
        }
        <?php if($parent_page): ?>
        mw.$('#mw_posts_create_live_edit').attr('parent-content-id', "<?php print $parent_page; ?>");
        <?php endif; ?>
        mw.$('#mw_posts_create_live_edit').attr('content-id', 0);
        mw.$('#mw_posts_create_live_edit').attr('quick_edit', 1);
        mw.$('#mw_posts_create_live_edit').removeAttr('live_edit');
        $('#mw_posts_edit_live_edit').html('');
        mw.load_module('content/edit', '#mw_posts_create_live_edit', function () {

            resizeModal();
        });


    }
    mw.manage_live_edit_content = function ($id) {
        Tabs.set(3);
        if ($id != undefined) {
            $('#mw_posts_manage_live_edit').attr('module-id', $id);
        }
        $('#mw_posts_manage_live_edit').removeAttr('just-saved');
        mw.load_module('content/manage_live_edit', '#mw_posts_manage_live_edit', function () {
            resizeModal();
        })
    }
    mw.edit_content_live_edit = function ($cont_id) {
        Tabs.set(4);
        mw.$('#mw_posts_edit_live_edit').empty();
        if(window.thismodal && thismodal.dialogContainer) {
            thismodal.dialogContainer.querySelector('iframe').style.height = 'auto';
            thismodal.dialogContainer.scrollTop = 0;
        }

        $('#mw_posts_edit_live_edit').attr('content-id', $cont_id);
        $('#mw_posts_edit_live_edit').removeAttr('live_edit');
        $('#mw_posts_edit_live_edit').attr('quick_edit', 1);

        $('#mw_posts_create_live_edit').html('');
        mw.load_module('content/edit', '#mw_posts_edit_live_edit', function () {
            resizeModal();
        });
    }

    mw.delete_content_live_edit = function (a, callback) {
        mw.tools.confirm("<?php _ejs("Do you want to delete this post"); ?>?", function () {
            var arr = $.isArray(a) ? a : [a];
            var obj = {ids: arr}
            $.post(mw.settings.site_url + "api/content/delete", obj, function (data) {
                typeof callback === 'function' ? callback.call(data) : '';
                $('.manage-post-item-' + a).fadeOut();
                mw.notification.warning("<?php _ejs('Content was sent to Trash'); ?>.");
                mw.reload_module_parent('posts')
                mw.reload_module_parent('shop/products')
                mw.reload_module_parent('content')
            });
        });
    }


    $(mwd).ready(function () {
        Tabs = mw.tabs({
            nav: ".post-settings-holder .mw-ui-btn-nav-tabs .mw-ui-btn",
            tabs: ".mw-ui-box-content-tabs .tab",
            activeClass: 'active-info',
            onclick: function (tab, event, index) {
                window.name = index;
            }
        });
        if (window.name != '') {
            var index = parseFloat(window.name);
            if (!isNaN(index)) {
                Tabs.set(index);
            }

        }
        resizeModal()
    });

</script>

<div class="post-settings-holder">
    <?php if (isset($params['global'])) { ?>
        <a href="javascript:;" class="mw-ui-btn" onclick="mw.add_new_content_live_edit('<?php print addslashes($set_content_type_mod); ?>');" style="position: absolute;top: 12px;right: 12px;z-index: 2;"><span class="mw-icon-<?php print trim($set_content_type_mod); ?>"></span>
            <?php _e("Add new"); ?>
            <?php _e(ucwords($set_content_type_mod)); ?></a>
    <?php } else if ($is_shop) { ?>
        <a href="javascript:;" class="mw-ui-btn mw-ui-btn-notification mw-ui-btn-medium pull-right" onclick="mw.add_new_content_live_edit('product');"><span class="mw-icon-product"></span>
            <?php _e("New Product"); ?>
        </a>
    <?php } else { ?>
        <a href="javascript:;" class="mw-ui-btn mw-ui-btn-notification mw-ui-btn-rounded mw-ui-btn-medium pull-right" onclick="mw.add_new_content_live_edit('post');">
            <span class="mw-icon-plus"></span> &nbsp;<?php _e("New Post"); ?>
        </a>
    <?php } ?>
    <div class="mw-ui-btn-nav mw-ui-btn-nav-tabs">
        <a href="javascript:;" class="mw-ui-btn active" onclick="javascript:mw.manage_live_edit_content('<?php print $params['id'] ?>');"><?php _e("Manage"); ?></a>
        <a href="javascript:;" class="mw-ui-btn"><?php _e("Settings"); ?></a>
        <a href="javascript:;" class="mw-ui-btn"><?php _e("Skin/Template"); ?></a>
    </div>
    <div class="mw-ui-box mw-ui-box-content mw-ui-box-content-tabs">
        <div class="tab" style="display: block">
            <module type="content/manager" <?php print $add_post_q ?> no_page_edit="true" id="mw_posts_manage_live_edit" no_toolbar="true"/>
        </div>
        <div class="tab" style="display:none">
            <?php include($posts_mod); ?>
        </div>
        <div class="tab" style="display:none">
            <?php if (isset($params['global'])) : ?>
                <module type="admin/modules/templates" id="posts_list_templ" for-module="posts"/>
            <?php else: ?>
                <module type="admin/modules/templates" id="posts_list_templ"/>
            <?php endif; ?>
        </div>
        <div class="tab">
            <div <?php print $add_post_q ?> id="mw_posts_create_live_edit"></div>
        </div>
        <div class="tab">
            <div id="mw_posts_edit_live_edit" class="mw_posts_edit_live_edit"></div>
        </div>
    </div>
</div>
