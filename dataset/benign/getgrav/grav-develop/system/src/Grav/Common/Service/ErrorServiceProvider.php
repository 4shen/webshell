<?php

/**
 * @package    Grav\Common\Service
 *
 * @copyright  Copyright (C) 2015 - 2019 Trilby Media, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Grav\Common\Service;

use Grav\Common\Errors\Errors;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ErrorServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container['errors'] = new Errors;
    }
}
