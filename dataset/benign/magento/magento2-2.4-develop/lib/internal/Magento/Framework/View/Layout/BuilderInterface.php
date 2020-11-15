<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout;

use Magento\Framework\View\LayoutInterface;

/**
 * Interface BuilderInterface
 *
 * @api
 */
interface BuilderInterface
{
    /**
     * Build structure
     *
     * @return LayoutInterface
     */
    public function build();
}
