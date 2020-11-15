<?php

/*
 * This file is part of the Zephir.
 *
 * (c) Phalcon Team <team@zephir-lang.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Zephir\Optimizers\FunctionCall;

/**
 * LtrimOptimizer.
 *
 * Optimizes calls to 'ltrim' using internal function
 */
class LtrimOptimizer extends TrimOptimizer
{
    protected static $TRIM_WHERE = 'ZEPHIR_TRIM_LEFT';
}
