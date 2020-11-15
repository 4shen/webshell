<?php

/*
 * This file is part of the Zephir.
 *
 * (c) Phalcon Team <team@zephir-lang.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Zephir\Optimizers;

use Zephir\Call;
use Zephir\CompilationContext;

/**
 * Class OptimizerAbstract.
 */
abstract class OptimizerAbstract
{
    /**
     * @param array              $expression
     * @param Call               $call
     * @param CompilationContext $context
     *
     * @return mixed
     */
    abstract public function optimize(array $expression, Call $call, CompilationContext $context);
}
