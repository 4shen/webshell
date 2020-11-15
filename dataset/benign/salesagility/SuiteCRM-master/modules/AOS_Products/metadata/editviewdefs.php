<?php
$module_name = 'AOS_Products';
$viewdefs [$module_name] =
array(
  'EditView' =>
  array(
    'templateMeta' =>
    array(
      'maxColumns' => '2',
      'widths' =>
      array(
        0 =>
        array(
          'label' => '10',
          'field' => '30',
        ),
        1 =>
        array(
          'label' => '10',
          'field' => '30',
        ),
      ),
      'form' =>
      array(
        'enctype' => 'multipart/form-data',
        'headerTpl' => 'modules/AOS_Products/tpls/EditViewHeader.tpl',
      ),
      'includes' =>
      array(
        0 =>
        array(
          'file' => 'modules/AOS_Products/js/products.js',
        ),
      ),
      'useTabs' => false,
      'tabDefs' =>
      array(
        'DEFAULT' =>
        array(
          'newTab' => false,
          'panelDefault' => 'expanded',
        ),
      ),
    ),
    'panels' =>
    array(
      'default' =>
      array(
        0 =>
        array(
          0 =>
          array(
            'name' => 'name',
            'label' => 'LBL_NAME',
          ),
          1 =>
          array(
            'name' => 'part_number',
            'label' => 'LBL_PART_NUMBER',
          ),
        ),
        1 =>
        array(
          0 =>
          array(
            'name' => 'aos_product_category_name',
            'label' => 'LBL_AOS_PRODUCT_CATEGORYS_NAME',
          ),
          1 =>
          array(
            'name' => 'type',
            'label' => 'LBL_TYPE',
          ),
        ),
        2 =>
        array(
          0 =>
          array(
            'name' => 'currency_id',
            'studio' => 'visible',
            'label' => 'LBL_CURRENCY',
          ),
        ),
        3 =>
        array(
          0 =>
          array(
            'name' => 'cost',
            'label' => 'LBL_COST',
          ),
          1 =>
          array(
            'name' => 'price',
            'label' => 'LBL_PRICE',
          ),
        ),
        4 =>
        array(
          0 =>
          array(
            'name' => 'contact',
            'label' => 'LBL_CONTACT',
          ),
          1 =>
          array(
            'name' => 'url',
            'label' => 'LBL_URL',
          ),
        ),
        5 =>
        array(
          0 =>
          array(
            'name' => 'description',
            'label' => 'LBL_DESCRIPTION',
          ),
        ),
        6 =>
        array(
          0 =>
          array(
            'name' => 'product_image',
            'customCode' => '{$PRODUCT_IMAGE}',
          ),
        ),
      ),
    ),
  ),
);
