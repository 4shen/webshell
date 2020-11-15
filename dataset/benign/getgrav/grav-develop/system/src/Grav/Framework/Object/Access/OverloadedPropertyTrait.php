<?php

/**
 * @package    Grav\Framework\Object
 *
 * @copyright  Copyright (C) 2015 - 2019 Trilby Media, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Grav\Framework\Object\Access;

/**
 * Overloaded Property Object Trait
 * @package Grav\Framework\Object\Access
 */
trait OverloadedPropertyTrait
{
    /**
     * Checks whether or not an offset exists.
     *
     * @param mixed $offset  An offset to check for.
     * @return bool          Returns TRUE on success or FALSE on failure.
     */
    public function __isset($offset)
    {
        return $this->hasProperty($offset);
    }

    /**
     * Returns the value at specified offset.
     *
     * @param mixed $offset  The offset to retrieve.
     * @return mixed         Can return all value types.
     */
    public function __get($offset)
    {
        return $this->getProperty($offset);
    }

    /**
     * Assigns a value to the specified offset.
     *
     * @param mixed $offset  The offset to assign the value to.
     * @param mixed $value   The value to set.
     */
    public function __set($offset, $value)
    {
        $this->setProperty($offset, $value);
    }

    /**
     * Magic method to unset the attribute
     *
     * @param mixed $offset The name value to unset
     */
    public function __unset($offset)
    {
        $this->unsetProperty($offset);
    }

    abstract public function hasProperty($property);
    abstract public function getProperty($property, $default = null);
    abstract public function setProperty($property, $value);
    abstract public function unsetProperty($property);
}
