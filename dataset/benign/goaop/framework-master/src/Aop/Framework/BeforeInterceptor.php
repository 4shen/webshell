<?php
declare(strict_types = 1);
/*
 * Go! AOP framework
 *
 * @copyright Copyright 2011, Lisachenko Alexander <lisachenko.it@gmail.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Go\Aop\Framework;

use Go\Aop\AdviceBefore;
use Go\Aop\Intercept\Joinpoint;

/**
 * "Before" interceptor
 *
 * @api
 */
final class BeforeInterceptor extends AbstractInterceptor implements AdviceBefore
{
    /**
     * @inheritdoc
     */
    public function invoke(Joinpoint $joinpoint)
    {
        ($this->adviceMethod)($joinpoint);

        return $joinpoint->proceed();
    }
}
