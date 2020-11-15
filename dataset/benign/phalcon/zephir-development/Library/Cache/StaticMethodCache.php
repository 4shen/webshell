<?php

/*
 * This file is part of the Zephir.
 *
 * (c) Phalcon Team <team@zephir-lang.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Zephir\Cache;

use Zephir\ClassMethod;
use Zephir\CompilationContext;

/**
 * StaticMethodCache.
 *
 * Calls in Zephir implement monomorphic and polymorphic caches to
 * improve performance. Method/Functions lookups are cached in a standard
 * first-level method lookup cache.
 *
 * The concept of inline caching is based on the empirical observation
 * that the objects that occur at a particular call site are often of the same type
 * Internal functions are considered monomorphic since they do not change across execution.
 * Final and private methods are also monomorphic because of their own nature.
 * Due to the Ahead-Of-Time compilation approach provided by Zephir, is not possible
 * to implement inline caches, however is possible to add barriers/guards to
 * take advantage of profile guided optimizations (PGO) and branch prediction.
 *
 * This implementation is based on the work of Hölzle, Chambers and Ungar [1].
 *
 * [1] http://www.cs.ucsb.edu/~urs/oocsb/papers/ecoop91.pdf
 */
class StaticMethodCache
{
    protected $cache = [];

    /**
     * MethodCache.
     *
     * @param CompilationContext            $compilationContext
     * @param ClassMethod|\ReflectionMethod $method
     * @param bool                          $allowNtsCache
     *
     * @return string
     */
    public function get(CompilationContext $compilationContext, $method, $allowNtsCache = true)
    {
        if (!\is_object($method)) {
            return 'NULL, 0';
        }

        if (!($method instanceof \ReflectionMethod)) {
            $completeName = $method->getClassDefinition()->getCompleteName();

            /*
             * Avoid generate caches for external classes
             */
            if ($method->getClassDefinition()->isExternal()) {
                return 'NULL, 0';
            }

            if (isset($this->cache[$completeName][$method->getName()])) {
                return '&'.$this->cache[$completeName][$method->getName()]->getName().', '.SlotsCache::getExistingMethodSlot($method);
            }

            if ($method->getClassDefinition()->isInterface()) {
                return 'NULL, 0';
            }
        }

        $mustBeCached = false;
        if (!$compilationContext->insideCycle) {
            if (!($method instanceof \ReflectionMethod)) {
                $classDefinition = $method->getClassDefinition();
                if (!$classDefinition->isBundled() && $allowNtsCache) {
                    $mustBeCached = !$compilationContext->backend->isZE3();
                } else {
                    if (!$method->isPrivate() && !$method->isFinal()) {
                        return 'NULL, 0';
                    }
                }
            } else {
                if (!$method->isPrivate() && !$method->isFinal()) {
                    return 'NULL, 0';
                }
            }
        }

        $functionCache = $compilationContext->symbolTable->getTempVariableForWrite('zephir_fcall_cache_entry', $compilationContext);

        if ($method->isPrivate() || $method->isFinal() || $mustBeCached) {
            $cacheSlot = SlotsCache::getMethodSlot($method);
        } else {
            $cacheSlot = '0';
        }

        $functionCache->setMustInitNull(true);
        $functionCache->setReusable(false);

        if (!($method instanceof \ReflectionMethod)) {
            $this->cache[$completeName][$method->getName()] = $functionCache;
        }

        return '&'.$functionCache->getName().', '.$cacheSlot;
    }
}
