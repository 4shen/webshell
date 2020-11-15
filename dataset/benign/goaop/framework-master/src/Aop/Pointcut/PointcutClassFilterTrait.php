<?php
declare(strict_types = 1);
/*
 * Go! AOP framework
 *
 * @copyright Copyright 2012, Lisachenko Alexander <lisachenko.it@gmail.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Go\Aop\Pointcut;

use Go\Aop\PointFilter;
use Go\Aop\Support\TruePointFilter;

/**
 * Convenient trait for pointcuts with class filter.
 *
 * The "classFilter" property can be set to customize ClassFilter behavior.
 */
trait PointcutClassFilterTrait
{
    /**
     * Filter for class
     *
     * @var null|PointFilter
     */
    protected $classFilter;

    /**
     * Set the ClassFilter to use for this pointcut.
     */
    public function setClassFilter(PointFilter $classFilter): void
    {
        $this->classFilter = $classFilter;
    }

    /**
     * Return the class filter for this pointcut.
     */
    public function getClassFilter(): PointFilter
    {
        if ($this->classFilter === null) {
            $this->classFilter = TruePointFilter::getInstance();
        }

        return $this->classFilter;
    }
}
