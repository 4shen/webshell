<?php

namespace Webkul\Shipping\Carriers;

use Illuminate\Support\Facades\Config;

abstract class AbstractShipping
{
    abstract public function calculate();

    /**
     * Checks if payment method is available
     *
     * @return array
     */
    public function isAvailable()
    {
        return $this->getConfigData('active');
    }

    /**
     * Returns payment method code
     *
     * @return array
     */
    public function getCode()
    {
        if (empty($this->code)) {
            // throw exception
        }

        return $this->code;
    }

    /**
     * Returns payment method title
     *
     * @return array
     */
    public function getTitle()
    {
        return $this->getConfigData('title');
    }

    /**
     * Returns payment method decription
     *
     * @return array
     */
    public function getDescription()
    {
        return $this->getConfigData('decription');
    }

    /**
     * Retrieve information from payment configuration
     *
     * @param  string  $field
     * @param  int|string|null  $channelId
     * @return mixed
     */
    public function getConfigData($field)
    {
        return core()->getConfigData('sales.carriers.' . $this->getCode() . '.' . $field);
    }
}