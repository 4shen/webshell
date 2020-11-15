<?php
/**
 * FecShop file.
 *
 * @link http://www.fecshop.com/
 * @copyright Copyright (c) 2016 FecShop Software LLC
 * @license http://www.fecshop.com/license/
 */
return [
    /**
     * Catalogsearch 模块的配置，您可以在@appfront/config/fecshop_local_modules/Catalogsearch.php 
     * 中进行配置，二开，或者重写该模块（在上面路径中如果文件不存在，自行新建配置文件。）
     */
    'catalogsearch' => [
        'class' => '\fecshop\app\appfront\modules\Catalogsearch\Module',
        /**
         * 模块内部的params配置。
         */
        'params'=> [
            //'categorysearch_filter_attr' =>[
            //	'color','size',
            //],
            // 搜索页面的title 格式 ，%s 将会被替换成搜索词
            'search_page_title_format' => 'Search Text: %s ',
            // 搜索页面的 meta keywords格式 ，%s 将会被替换成搜索词
            'search_page_meta_keywords_format' => 'Search Text: %s ',
            // 搜索页面的 meta description格式 ，%s 将会被替换成搜索词
            'search_page_meta_description_format' => 'Search Text: %s ',
            // 搜索的最大个数
            'product_search_max_count'  => 1000,
            // 搜索页面是否开启面包屑导航
            'search_breadcrumbs'        => true,
        ],
    ],
];
