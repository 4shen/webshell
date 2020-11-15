<?php

/**
 * @package    Grav\Common
 *
 * @copyright  Copyright (C) 2015 - 2019 Trilby Media, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Grav\Common;

/**
 * @deprecated 1.4 Use Grav::instance() instead.
 */
trait GravTrait
{
    protected static $grav;

    /**
     * @return Grav
     * @deprecated 1.4 Use Grav::instance() instead.
     */
    public static function getGrav()
    {
        user_error(__TRAIT__ . ' is deprecated since Grav 1.4, use Grav::instance() instead', E_USER_DEPRECATED);

        if (!self::$grav) {
            self::$grav = Grav::instance();
        }

        return self::$grav;
    }
}
