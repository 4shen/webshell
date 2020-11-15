<?php
/**
 * FecShop file.
 *
 * @link http://www.fecshop.com/
 * @copyright Copyright (c) 2016 FecShop Software LLC
 * @license http://www.fecshop.com/license/
 */

namespace fecshop\app\apphtml5\modules\Customer\block\account;

use Yii;

/**
 * @author Terry Zhao <2358269014@qq.com>
 * @since 1.0
 */
class Resetpasswordsuccess
{
    public function getLastData()
    {
        return [
            'loginUrl' => Yii::$service->url->getUrl('customer/account/login'),
        ];
    }
}
