<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Block\Cart\Item\Renderer\Actions;

/**
 * @api
 */
class Edit extends Generic
{
    /**
     * Get item configure url
     *
     * @return string
     */
    public function getConfigureUrl()
    {
        return $this->getUrl(
            'checkout/cart/configure',
            [
                'id' => $this->getItem()->getId(),
                'product_id' => $this->getItem()->getProduct()->getId()
            ]
        );
    }
}
