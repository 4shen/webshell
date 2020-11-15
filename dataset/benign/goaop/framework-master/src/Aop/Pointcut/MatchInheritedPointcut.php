<?php
declare(strict_types = 1);
/*
 * Go! AOP framework
 *
 * @copyright Copyright 2016, Lisachenko Alexander <lisachenko.it@gmail.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Go\Aop\Pointcut;

use Go\Aop\Pointcut;
use Go\Aop\PointFilter;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

/**
 * Pointcut that matches all inherited items, this is useful to filter inherited memebers via !matchInherited()
 */
class MatchInheritedPointcut implements Pointcut
{
    use PointcutClassFilterTrait;

    /**
     * Performs matching of point of code
     *
     * @param mixed $point Specific part of code, can be any Reflection class
     * @param null|mixed $context Related context, can be class or namespace
     * @param null|string|object $instance Invocation instance or string for static calls
     * @param null|array $arguments Dynamic arguments for method
     */
    public function matches($point, $context = null, $instance = null, array $arguments = null): bool
    {
        if (!$context instanceof ReflectionClass) {
            return false;
        }

        $isPointMethod   = $point instanceof ReflectionMethod;
        $isPointProperty = $point instanceof ReflectionProperty;
        if (!$isPointMethod && !$isPointProperty) {
            return false;
        }

        $declaringClassName = $point->getDeclaringClass()->name;

        return $context->name !== $declaringClassName && $context->isSubclassOf($declaringClassName);
    }

    /**
     * Returns the kind of point filter
     */
    public function getKind(): int
    {
        return PointFilter::KIND_METHOD | PointFilter::KIND_PROPERTY;
    }
}
