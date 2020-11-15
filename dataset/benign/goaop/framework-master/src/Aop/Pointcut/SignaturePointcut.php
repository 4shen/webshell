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

use Go\Aop\Pointcut;
use Go\Aop\PointFilter;

/**
 * Signature pointcut checks element signature (modifiers and name) to match it
 */
class SignaturePointcut implements Pointcut
{
    use PointcutClassFilterTrait;

    /**
     * Element name to match, can contain wildcards **,*,?,|
     */
    protected $name = '';

    /**
     * Regular expression for pattern matching
     */
    protected $regexp;

    /**
     * Modifier filter for element
     */
    protected $modifierFilter;

    /**
     * Filter kind, e.g. self::KIND_CLASS
     */
    protected $filterKind = 0;

    /**
     * Signature matcher constructor
     */
    public function __construct(int $filterKind, string $name, PointFilter $modifierFilter)
    {
        $this->filterKind = $filterKind;
        $this->name       = $name;
        $this->regexp     = strtr(preg_quote($this->name, '/'), [
            '\\*'    => '[^\\\\]+?',
            '\\*\\*' => '.+?',
            '\\?'    => '.',
            '\\|'    => '|'
        ]);
        $this->modifierFilter = $modifierFilter;
    }

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
        if (!$this->modifierFilter->matches($point, $context)) {
            return false;
        }

        return ($point->name === $this->name) || (bool) preg_match("/^(?:{$this->regexp})$/", $point->name);
    }

    /**
     * Returns the kind of point filter
     */
    public function getKind(): int
    {
        return $this->filterKind;
    }
}
