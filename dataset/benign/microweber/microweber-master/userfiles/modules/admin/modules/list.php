<?php
$modules_options = array();
$modules_options['skip_admin'] = true;
$modules_options['ui'] = true;

$modules = array();
$modules_by_categories = array();
$mod_obj_str = 'modules';

$template_config = mw()->template->get_config();

$show_grouped_by_cats = false;
$hide_dynamic_layouts = false;
$disable_elements = false;
if(isset($template_config['elements_mode']) and $template_config['elements_mode'] == 'disabled'){
//dd($template_config);
    $disable_elements = true;

}

if(isset($params['hide-dynamic']) and $params['hide-dynamic']){
    $hide_dynamic_layouts = true;
}

if (isset($is_elements) and $is_elements == true) {
    $mod_obj_str = 'elements';
    $el_params = array();
    if (isset($params['layout_type'])) {
        $el_params['layout_type'] = $params['layout_type'];
    }
    $modules = mw()->layouts_manager->get($el_params);
    //$modules = false;

    if ($modules == false) {
        // scan_for_modules($modules_options);
        $el_params['no_cache'] = true;
                mw()->modules->scan_for_elements($el_params);
        $modules = mw()->layouts_manager->get($el_params);


    }





   // dd($modules);

    if ($modules == false) {
        $modules = array();
    }

    $elements_from_template = mw()->layouts_manager->get_elements_from_current_site_template();
    if (!empty($elements_from_template)) {

        $modules = array_merge($elements_from_template,$modules);

    }



    if($disable_elements){
    $modules = array();
}


    // REMOVE
    //$modules = array();
//return;

    // $dynamic_layouts = mw()->layouts_manager->get_all('no_cache=1&get_dynamic_layouts=1');
    $dynamic_layouts = false;
    $module_layouts_skins = false;
    $dynamic_layouts = mw()->layouts_manager->get_all('no_cache=1&get_dynamic_layouts=1');
      $module_layouts_skins = mw()->modules->templates('layouts');
    if($hide_dynamic_layouts){
         $dynamic_layouts =  false;
         $module_layouts_skins =  false;
    }



    // $module_layouts_skins_def = mw()->modules->templates('layouts',false, false, 'module_dir');
    //$module_layouts_skins_def = mw()->modules->templates('layouts',false, false, 'dream');
//var_dump($module_layouts_skins_def);
//    if(is_array($module_layouts_skins) and is_arr($module_layouts_skins_def) and ($module_layouts_skins != $module_layouts_skins_def)){
//        $module_layouts_skins = array_merge($module_layouts_skins,$module_layouts_skins_def);
//    }


} else {
    $modules = mw()->modules->get('installed=1&ui=1');
    $module_layouts = mw()->modules->get('installed=1&module=layouts');


    $hide_from_display_list = array('layouts','template_settings');

    $sortout_el = array();
    $sortout_mod = array();
    if (!empty($modules)) {
        foreach ($modules as $mod) {
            if (isset($mod['as_element']) and intval($mod['as_element']) == 1) {
                $sortout_el[] = $mod;
            } else {
                $sortout_mod[] = $mod;
            }
        }
        $modules = array_merge($sortout_el, $sortout_mod);
        if ($modules and !empty($module_layouts)) {
            $modules = array_merge($modules, $module_layouts);
        }
    }





    $modules_from_template = mw()->modules->get_modules_from_current_site_template();
    if (!empty($modules_from_template)) {
        if (!is_array($modules)) {
            $modules = array();
        }
        foreach ($modules as $module) {
            foreach ($modules_from_template as $k => $module_from_template) {
                if (isset($module['name']) and isset($module_from_template['name'])) {
                    if ($module['name'] == $module_from_template['name']) {
                        unset($modules_from_template[$k]);
                    }
                }
            }
        }
        $modules = array_merge($modules, $modules_from_template);
    }

    $is_shop_disabled = get_option('shop_disabled', 'website') == "y";

    if ($modules) {
        foreach ($modules as $mkey=> $module) {
            if (!isset($module['categories']) or !($module['categories'])) {
                $module['categories'] = 'other';
            }
            if (isset($module['categories']) and ($module['categories'])) {
                $mod_cats = explode(',', $module['categories']);

                if ($mod_cats) {
                    $skip_m = false;
                    if ($is_shop_disabled and in_array('online shop', $mod_cats)) {
                        $skip_m = true;
                    }

                    if (!$skip_m) {
                        foreach ($mod_cats as $mod_cat) {
                            $mod_cat = trim($mod_cat);
                            if (!isset($modules_by_categories[$mod_cat])) {
                                $modules_by_categories[$mod_cat] = array();
                            }
                            $modules_by_categories[$mod_cat][] = $module;
                        }

                    } else {
                        unset($modules[$mkey]);
                    }
                }
            }
        }
    }





}


if ($modules_by_categories and is_arr($modules_by_categories) and count($modules_by_categories) > 1) {
    $sort_first = array();

    $first_keys = array('recommended', 'media', 'content', 'navigation');
    foreach ($first_keys as $first_key) {
        if (isset($modules_by_categories[$first_key])) {
            $sort_first[$first_key] = $modules_by_categories[$first_key];
            unset($modules_by_categories[$first_key]);
        }
    }
    $modules_by_categories_new = array_merge($sort_first, $modules_by_categories);
    $modules_by_categories = $modules_by_categories_new;
}


if (($modules and !$modules_by_categories) or ($modules and !$show_grouped_by_cats)) {
    $modules_by_categories = array('Modules' => $modules);
}




if (isset($_COOKIE['recommend']) and is_string($_COOKIE['recommend']) and isset($modules) and is_array($modules)) {
    $recommended = json_decode($_COOKIE['recommend'], true);

    if (is_array($recommended) and !empty($recommended)) {
        $position = 9;
        $sorted_modules = array();
        arsort($recommended);
        foreach ($recommended as $key => $value) {
            foreach ($modules as $mod_key => $item) {
                if (isset($item['module']) and isset($item['position']) and $item['position'] > $position) {
                    if ($key == $item['module']) {
                        $sorted_modules[] = $item;
                    }
                }
            }
        }

        if (!empty($sorted_modules)) {
            //arsort( $sorted_modules);
            if (!empty($modules)) {
                $re_sorted_modules = array();
                $temp = array();
                $modules_copy = $modules;
                foreach ($modules_copy as $key => $item) {
                    if (is_array($sorted_modules) and !empty($sorted_modules)) {
                        foreach ($sorted_modules as $key2 => $sorted_module) {
                            if ($sorted_module['module'] == $item['module']) {
                                unset($modules_copy[$key]);
                            }
                        }
                    }
                }
                foreach ($modules_copy as $key => $item) {
                    $re_sorted_modules[] = $item;

                    if (!isset($item['position'])) {
                        $item['position'] = 999;
                    }

                    if ($item['position'] > $position) {
                        if (is_array($sorted_modules) and !empty($sorted_modules)) {
                            foreach ($sorted_modules as $key2 => $sorted_module) {
                                $re_sorted_modules[] = $sorted_module;
                                unset($sorted_modules[$key2]);
                            }
                        }

                    }

                }
                if (!empty($re_sorted_modules)) {
                    $modules = $re_sorted_modules;
                }
            }
        }
    }

}


?>  <?php if (!isset($params['clean'])) { ?>
    <script type="text/javascript">

        Modules_List_<?php print $mod_obj_str ?> = {}
        mw.live_edit.registry = mw.live_edit.registry || {}

    </script>

<?php } ?>

<ul class="modules-list list-<?php print $mod_obj_str ?>" ocr="off">
    <?php
    $def_icon = modules_path() . 'default.jpg';
    $def_icon = mw()->url_manager->link_to_file($def_icon);



    ?>
    <?php if (isset($dynamic_layouts) and is_array($dynamic_layouts)): ?>
        <?php


        $i = 0; ?>
        <?php foreach ($dynamic_layouts as $dynamic_layout): ?>
            <?php if (isset($dynamic_layout['template_dir']) and isset($dynamic_layout['layout_file'])): ?>
                <li data-module-name="layout"
                    template="<?php print $dynamic_layout['template_dir'] ?>/<?php print $dynamic_layout['layout_file'] ?>"
                    data-filter="<?php print $dynamic_layout['name'] ?>" class="module-item" unselectable="on"> <span
                            class="mw_module_hold">
    <?php if (!isset($dynamic_layout['icon'])): ?>
        <?php $dynamic_layout['icon'] = $def_icon; ?>
    <?php endif; ?>
                        <span class="mw_module_image"> <span class="mw_module_image_holder">
    <img
            alt="<?php print $dynamic_layout['name'] ?>"
            title="<?php isset($dynamic_layout['description']) ? print addslashes($dynamic_layout['description']) : print addslashes($dynamic_layout['name']); ?>"
            class="module_draggable"
            data-module-name-enc="static_layout_<?php print date("YmdHis") . $i++ ?>"
            data-module-name="<?php print $dynamic_layout['layout_file'] ?>"
            src="<?php print $dynamic_layout['icon'] ?>"
    /> </span></span> <span class="module_name"
                            alt="<?php isset($dynamic_layout['description']) ? print addslashes($dynamic_layout['description']) : ''; ?>">
    <?php print titlelize(_e($dynamic_layout['name'], true)); ?>
    </span> </span></li>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>


    <script>
        $(document).ready(function () {
            $('.mw_module_image img').each(function (index) {
                var img = $(this).data('src');
                $(this).attr('src', img);
            });
        });
    </script>


    <?php

    if (isset($module_layouts_skins) and is_array($module_layouts_skins)): ?>
        <?php



        $i = 0; ?>

        <?php


        foreach ($module_layouts_skins as $dynamic_layout): ?>
            <?php if (isset($dynamic_layout['layout_file'])): ?>
                <li data-module-name="layouts" ondrop="true" template="<?php print $dynamic_layout['layout_file'] ?>"
                    data-filter="<?php print $dynamic_layout['name'] ?>" class="module-item module-item-layout"
                    unselectable="on">
                    <span class="mw_module_hold">
                        <?php



                        if (!isset($dynamic_layout['screenshot'])): ?>
                            <?php $dynamic_layout['screenshot'] = $def_icon; ?>
                        <?php endif; ?>
                        <span class="mw_module_image">
                            <span class="mw_module_image_holder">
                                <img
                                        alt="<?php print $dynamic_layout['name'] ?>"
                                        title="<?php isset($dynamic_layout['description']) ? print addslashes($dynamic_layout['description']) : print addslashes($dynamic_layout['name']); ?> [<?php print str_replace('.php', '', $dynamic_layout['layout_file']); ?>]"
                                        class="module_draggable"
                                        data-module-name-enc="layout_<?php print date("YmdHis") . $i++ ?>"
                                        data-module-name="layouts"
                                        ondrop="true"
                                        src=""
                                        data-src="<?php print thumbnail($dynamic_layout['screenshot'], 340, 340) ?>"
                                />
                            </span>
                        </span>
                        <span class="module_name"
                              alt="<?php isset($dynamic_layout['description']) ? print addslashes($dynamic_layout['description']) : ''; ?>"><?php print titlelize(_e($dynamic_layout['name'], true)); ?></span>
                    </span>
                </li>
            <?php


            endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>


    <?php if (isset($modules) and !empty($modules)): ?>


        <?php foreach ($modules_by_categories as $mod_cat => $modules) : ?>

            <?php if ($mod_obj_str == 'modules' and count($modules_by_categories) > 1): ?>

                <li class=" " unselectable="on" style="width: 100%; position: relative; float: left; padding: 0px">
                    <hr>
                    <h4 onclick="$('.module-cat-toggle-<?php print($mod_cat); ?>').toggle()"><?php print ucwords(_e($mod_cat, true)); ?> </h4>
                    <hr>
                </li>


            <?php endif; ?>


            <?php if ($mod_obj_str == 'elements'): ?>
                <li unselectable="on"  >
                    <hr>
                    <h4 onclick="$('.default-layouts', '.list-<?php print $mod_obj_str ?>').toggle()"><?php _e('Default static layouts'); ?> </h4>
                    <hr>
                </li>

            <?php endif; ?>

            <?php $i = 0; ?>
            <?php foreach ($modules as $module_item): ?>
                <?php $i++; ?>
                <?php if (isset($module_item['module'])): ?>
                    <?php


                    $module_group2 = explode(DIRECTORY_SEPARATOR, $module_item['module']);
                    $module_group2 = $module_group2[0];
                    ?>
                    <?php $module_item['module'] = str_replace('\\', '/', $module_item['module']);

                    $module_item['module'] = rtrim($module_item['module'], '/');
                    $module_item['module'] = rtrim($module_item['module'], '\\');
                    $temp = array();
                    if (isset($module_item['categories']) and is_array($module_item['categories']) and !empty($module_item['categories'])) {
                        foreach ($module_item['categories'] as $it) {
                            $temp[] = $it['parent_id'];
                        }
                        $module_item['categories'] = implode(',', $temp);
                    }

                    ?>
                    <?php $module_item['module_clean'] = str_replace('/', '__', $module_item['module']); ?>
                    <?php $module_item['name_clean'] = str_replace('/', '-', $module_item['module']); ?>
                    <?php $module_item['name_clean'] = str_replace(' ', '-', $module_item['name_clean']);
                    if (isset($module_item['categories']) and is_array($module_item['categories'])) {
                        $module_item['categories'] = implode(',', $module_item['categories']);
                    }

                    if (!isset($module_item['description'])) {
                        $module_item['description'] = $module_item['name'];
                    }

                    ?>
                    <?php $module_id = $module_item['name_clean'] . '_' . uniqid($i); ?>
                    <li    <?php if (isset($hide_from_display_list) and in_array($module_item['module'],$hide_from_display_list)) { ?> style="display: none"   <?php } ?>   <?php if (!isset($params['clean'])) { ?> id="<?php print $module_id; ?>" <?php } ?>
                            data-module-name="<?php print $module_item['module'] ?>"

                        <?php if ($mod_obj_str == 'elements'): ?> style="" <?php endif; ?>
                            data-filter="<?php print $module_item['name'] ?>"
                            ondrop="true"
                            data-category="<?php isset($module_item['categories']) ? print addslashes($module_item['categories']) : ''; ?>"
                    <?php if (isset($module_item['template'])) { ?>
                            template="<?php print $module_item['template'] ?>"
                    <?php } ?>
                            class="module-item module-item-module module-cat-toggle-<?php print $mod_cat ?> <?php if ($mod_obj_str == 'elements'): ?>default-layouts<?php endif; ?><?php if (isset($module_item['as_element']) and intval($module_item['as_element'] == 1) or (isset($is_elements) and $is_elements == true)) : ?> module-as-element<?php endif; ?>">
                    <span unselectable="on" class="mw_module_hold"
                          title="<?php print addslashes($module_item["name"]); ?>. <?php print addslashes($module_item["description"]) ?>">



  <?php if (!isset($params['clean'])): ?>

      <?php
      $t = $module_item["name"];
      $t2 = _e($module_item["name"], true);
      if ($t2 and $t != $t2) {
        //  $t = $t . ' / ' . $t2;
          $t=$t2;
      }

      $t = str_replace("\r", "", $t);

      $t = str_replace("\n", "", $t);


      ?>
      <script type="text/javascript">
          Modules_List_<?php print $mod_obj_str ?>['<?php print $module_item["module"] ?>'] = {
              id: '<?php print($module_id); ?>',
              name: '<?php print $module_item["module"] ?>',
              title: '<?php print addslashes($t); ?>',
              <?php

              // do not populate the icon for elements as its 1mb+
              if($mod_obj_str != 'element') :  ?>
              icon: '<?php isset($module_item['icon']) ? print ($module_item['icon']) : ''; ?>',
              <?php endif; ?>
              <?php if (isset($module_item['settings']) and is_array($module_item['settings']) and !empty($module_item['settings'])): ?>
              settings: <?php print json_encode($module_item['settings']) ?>,

              <?php endif; ?>
              description: '<?php print addslashes($module_item["description"]) ?>'
          }

          mw.live_edit.registry['<?php print $module_item["module"] ?>'] = Modules_List_<?php print $mod_obj_str ?>['<?php print $module_item["module"] ?>'];


          <?php if (isset($module_item['settings']) and is_array($module_item['settings']) and !empty($module_item['settings'])): ?>
          if (typeof(mw.live_edit_module_settings_array) != 'undefined') {
              mw.live_edit_module_settings_array['<?php print $module_item["module"] ?>'] = <?php print json_encode($module_item['settings']) ?>
          }
          <?php endif; ?>
      </script>


  <?php endif; ?>

                        <?php if (isset($module_item['icon']) AND $module_item['icon']): ?>
                            <span class="mw_module_image">
        <span class="mw_module_image_holder">
            <img
                    alt="<?php print $module_item['name'] ?>"
                    title="<?php isset($module_item['description']) ? print addslashes($module_item['description']) : ''; ?>"
                    class="module_draggable"
                    data-module-name-enc="<?php print $module_item['module_clean'] ?>|<?php print $module_item['name_clean'] ?>_<?php print date("YmdHis") ?>"
                    data-src="<?php print $module_item['icon']; ?>"/>
        </span>
    </span>
                        <?php endif; ?>
                        <span class="module_name"
                              alt="<?php isset($module_item['description']) ? print addslashes($module_item['description']) : ''; ?>">
                <?php if ($mod_obj_str == 'elements'): ?>
                    <?php print character_limiter(_e($module_item['name'], true), 13); ?>
                <?php else: ?>
                    <?php _e($module_item['name']); ?>
                <?php endif; ?>
    </span> </span></li>
                <?php endif; ?>
            <?php endforeach; ?>


        <?php endforeach; ?>


    <?php endif; ?>


</ul>


