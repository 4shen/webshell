<?php

namespace Bolt\Storage\Field\Collection;

use Bolt\Storage\Entity\FieldValue;
use Doctrine\Common\Collections\Collection;

/**
 * A map of FieldValues.
 *
 * @author Carson Full <carsonfull@gmail.com>
 */
interface FieldCollectionInterface extends Collection
{
    /**
     * @return int[]
     */
    public function getNew();

    /**
     * @return int[]
     */
    public function getExisting();

    /**
     * @param int $grouping
     */
    public function setGrouping($grouping);

    /**
     * Adds a field value to the map.
     *
     * @param FieldValue $value
     *
     * @return bool
     */
    public function add($value);

    /**
     * Adds a field value to the map with the specified key.
     *
     * @param string     $key
     * @param FieldValue $value
     */
    public function set($key, $value);

    /**
     * @return \Iterator|FieldValue[]
     */
    public function getIterator();

    /**
     * Returns the type of a given $fieldName.
     *
     * @param string $fieldName
     *
     * @return string|null
     */
    public function getFieldType($fieldName);

    /**
     * Returns the rendered version of a value for Twig.
     *
     * @internal
     *
     * This can be refactored to something better, once the
     * deprecation of legacy content is complete
     *
     * @param string $fieldName
     *
     * @return mixed
     */
    public function getRenderedValue($fieldName);
}
