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

use Go\Aop\IntroductionAdvisor;
use Go\Aop\IntroductionInfo;
use Go\Aop\Pointcut\PointcutClassFilterTrait;
use Go\Aop\PointFilter;

/**
 * Introduction advisor delegating to the given object.
 */
class DeclareParentsAdvisor extends AbstractGenericAdvisor implements IntroductionAdvisor
{
    use PointcutClassFilterTrait;

    /**
     * Creates an advisor for declaring mixins via trait and interface.
     */
    public function __construct(PointFilter $classFilter, IntroductionInfo $info)
    {
        $this->classFilter = $classFilter;
        parent::__construct($info);
    }
}
