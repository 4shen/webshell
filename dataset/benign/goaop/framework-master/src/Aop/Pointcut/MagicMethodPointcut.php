<?php
declare(strict_types = 1);
/*
 * Go! AOP framework
 *
 * @copyright Copyright 2014, Lisachenko Alexander <lisachenko.it@gmail.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Go\Aop\Pointcut;

use Go\Aop\Pointcut;
use Go\Aop\PointFilter;

/**
 * Magic method pointcut is a dynamic checker that verifies calls for __call and __callStatic
 */
class MagicMethodPointcut implements PointFilter, Pointcut
{
    use PointcutClassFilterTrait;

    /**
     * Method name to match, can contain wildcards *,?
     */
    protected $methodName = '';

    /**
     * Regular expression for matching
     */
    protected $regexp;

    /**
     * Modifier filter for method
     */
    protected $modifierFilter;

    /**
     * Magic method matcher constructor
     *
     * NB: only public methods can be matched as __call and __callStatic are public
     */
    public function __construct(string $methodName, PointFilter $modifierFilter)
    {
        $this->methodName     = $methodName;
        $this->regexp         = strtr(preg_quote($this->methodName, '/'), [
            '\\*' => '.*?',
            '\\?' => '.',
            '\\|' => '|'
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
        // With single parameter (statically) always matches for __call, __callStatic
        if ($instance === null) {
            return ($point->name === '__call' || $point->name === '__callStatic');
        }

        if (!$this->modifierFilter->matches($point)) {
            return false;
        }

        // for __call and __callStatic method name is the first argument on invocation
        [$methodName] = $arguments;

        return ($methodName === $this->methodName) || (bool) preg_match("/^(?:{$this->regexp})$/", $methodName);
    }

    /**
     * Returns the kind of point filter
     */
    public function getKind(): int
    {
        return PointFilter::KIND_METHOD | PointFilter::KIND_DYNAMIC;
    }
}
