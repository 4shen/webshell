<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\CustomerData;

use Magento\Quote\Model\Quote\Item;

/**
 * Item interface
 *
 * @api
 */
interface ItemInterface
{
    /**
     * Get item data by quote item
     *
     * @param Item $item
     * @return array
     */
    public function getItemData(Item $item);
}
