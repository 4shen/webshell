<?php

/**
 * @package    Grav\Framework\Collection
 *
 * @copyright  Copyright (C) 2015 - 2019 Trilby Media, LLC. All rights reserved.
 * @license    MIT License; see LICENSE file for details.
 */

namespace Grav\Framework\Collection;

use Doctrine\Common\Collections\AbstractLazyCollection as BaseAbstractLazyCollection;

/**
 * General JSON serializable collection.
 *
 * @package Grav\Framework\Collection
 */
abstract class AbstractLazyCollection extends BaseAbstractLazyCollection implements CollectionInterface
{
    /**
     * The backed collection to use
     *
     * @var ArrayCollection
     */
    protected $collection;

    /**
     * {@inheritDoc}
     */
    public function reverse()
    {
        $this->initialize();
        return $this->collection->reverse();
    }

    /**
     * {@inheritDoc}
     */
    public function shuffle()
    {
        $this->initialize();
        return $this->collection->shuffle();
    }

    /**
     * {@inheritDoc}
     */
    public function chunk($size)
    {
        $this->initialize();
        return $this->collection->chunk($size);
    }

    /**
     * {@inheritDoc}
     */
    public function select(array $keys)
    {
        $this->initialize();
        return $this->collection->select($keys);
    }

    /**
     * {@inheritDoc}
     */
    public function unselect(array $keys)
    {
        $this->initialize();
        return $this->collection->unselect($keys);
    }

    /**
     * {@inheritDoc}
     */
    public function jsonSerialize()
    {
        $this->initialize();
        return $this->collection->jsonSerialize();
    }
}
