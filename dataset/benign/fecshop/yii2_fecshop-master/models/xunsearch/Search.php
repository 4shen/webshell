<?php
/**
 * FecShop file.
 *
 * @link http://www.fecshop.com/
 * @copyright Copyright (c) 2016 FecShop Software LLC
 * @license http://www.fecshop.com/license/
 */

namespace fecshop\models\xunsearch;

/**
 * @author Terry Zhao <2358269014@qq.com>
 * @since 1.0
 */
class Search extends \hightman\xunsearch\ActiveRecord
{
    public static function projectName()
    {
        return 'search';    // 这将使用 @fecshop/config/xunsearch/search.ini 作为项目名
    }
}
