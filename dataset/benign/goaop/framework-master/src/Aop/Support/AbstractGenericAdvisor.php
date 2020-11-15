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

namespace Go\Aop\Support;

use Go\Aop\Advice;
use Go\Aop\Advisor;

/**
 * Abstract generic Advisor that allows for any Advice to be configured.
 */
abstract class AbstractGenericAdvisor implements Advisor
{
    /**
     * Instance of advice
     */
    protected $advice;

    /**
     * Initializes an advisor with advice
     */
    public function __construct(Advice $advice)
    {
        $this->advice = $advice;
    }

    /**
     * Returns an advice to apply
     */
    public function getAdvice(): Advice
    {
        return $this->advice;
    }
}
