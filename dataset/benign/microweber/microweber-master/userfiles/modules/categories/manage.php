<div class="section-header">
    <h2 class="pull-left"><span class="mw-icon-category"></span> <?php _e("Categories"); ?></h2>
</div>

<div class="mw-module-category-manager admin-side-content">
    <div class="mw-ui-box mw-ui-settings-box mw-ui-box-content">

        <div class="mw-ui-row m-b-20">
            <div class="mw-ui-col text-left" style="width: 445px;">
                <a href="#" onclick="mw.quick_cat_edit_create(0);" class="mw-ui-btn mw-ui-btn-outline mw-ui-btn-info"><i class="mw-icon-plus"></i>&nbsp; <?php _e("New category"); ?></a>
            </div>
        </div>

        <div>
            <div class="mw-searchbox">
                <div class="mw-sb-item">
                    <div class="mw-sb-item-input"><input type="text" class="mw-ui-field" placeholder="<?php _e("Search"); ?>" oninput="categorySearch(this)"/></div>
                </div>
            </div>

            <script>
                $(document).ready(function () {
                    mw.admin.scrollBox(".mw-ui-category-selector");
                })
            </script>
            <div class="mw-ui-category-selector mw-ui-manage-list m-0" id="mw-ui-category-selector-manage" style="visibility: visible;display: block">
                <?php
                $field_name = "categories";
                $selected = 0;
                $tree = array();
                $tree['ul_class'] = 'pages_tree cat_tree_live_edit';
                $tree['li_class'] = 'sub-nav';
                $tree['rel_type'] = 'content';

                if (isset($params['page-id']) and $params['page-id'] != false) {
                    $tree['rel_id'] = intval($params['page-id']);
                }


                $tree['link'] = "<a href='javascript:mw.quick_cat_edit({id})'><span class='mw-icon-category'></span>&nbsp;{title}</a>";
                category_tree($tree);
                ?>
            </div>
            <script type="text/javascript">

                categorySearch = function (el) {
                    var val = el.value.trim().toLowerCase();
                    if (!val) {
                        $(".mw-ui-category-selector a").show()
                    }
                    else {
                        $(".mw-ui-category-selector a").each(function () {
                            var currel = $(this);
                            var curr = currel.text().trim().toLowerCase();
                            if (curr.indexOf(val) !== -1) {
                                currel.show()
                            }
                            else {
                                currel.hide()
                            }
                        })
                    }
                }


                mw.live_edit_load_cats_list = function () {
                    mw.load_module('categories/manage', '#mw_add_cat_live_edit', function () {

                    });
                }
                mw.quick_cat_edit = function (id) {

                    if (!!id) {
                        var modalTitle = '<?php _e('Edit category'); ?>';
                    } else {
                        var modalTitle = '<?php _e('Add category'); ?>';
                    }

                    mw_admin_edit_category_item_module_opened = mw.modal({
                        content: '<div id="mw_admin_edit_category_item_module"></div>',
                        title: modalTitle,
                        id: 'mw_admin_edit_category_item_popup_modal'
                    });

                    var params = {}
                    params['data-category-id'] = id;
                    params['no-toolbar'] = true;
                    mw.load_module('categories/edit_category', '#mw_admin_edit_category_item_module', null, params);
                }

                mw.quick_cat_edit_create = mw.quick_cat_edit_create || function (id) {
                        return mw.quick_cat_edit(id);
                        <?php if(isset($params['page-id']) and $params['page-id'] != false): ?>
                        //mw.$("#mw_edit_category_admin_holder").attr("page-id", '<?php print $params['page-id'] ?>');

                        <?php endif; ?>

                    }
            </script>

            <script type="text/javascript">
                mw.on.moduleReload("<?php print $params['id'] ?>", function () {
                    mw.manage_cat_sort();
                    $(".mw-ui-category-selector a").append('<span class="category-edit-label">' + mw.msg.edit + ' ' + mw.msg.category + '</span>')
                });

                mw.manage_cat_sort = function () {
                    mw.$("#<?php print $params['id'] ?>").sortable({
                        items: '.category_element',
                        axis: 'y',
                        handle: 'a',
                        update: function () {
                            var obj = {ids: []}
                            $(this).find('.category_element').each(function () {
                                var id = this.attributes['value'].nodeValue;
                                obj.ids.push(id);
                            });
                            $.post("<?php print api_link('category/reorder'); ?>", obj, function () {
                                if (self !== parent && !!parent.mw) {
                                    parent.mw.reload_module('categories');
                                }
                            });
                        },
                        start: function (a, ui) {
                            $(this).height($(this).outerHeight());
                            $(ui.placeholder).height($(ui.item).outerHeight())
                            $(ui.placeholder).width($(ui.item).outerWidth())
                        },
                        scroll: false
                    });
                }
                //mw.manage_cat_sort();

            </script>
        </div>
    </div>
    <div id="mw_edit_category_admin_holder"></div>
</div>