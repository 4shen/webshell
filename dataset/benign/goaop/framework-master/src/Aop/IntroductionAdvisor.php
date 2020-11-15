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

namespace Go\Aop;

/**
 * Superinterface for advisors that perform one or more AOP introductions.
 *
 * This interface cannot be implemented directly; subinterfaces must provide the advice type
 * implementing the introduction.
 *
 * Introduction is the implementation of additional interfaces (not implemented by a target) via AOP advice.
 */
interface IntroductionAdvisor extends Advisor
{

    /**
     * Returns the filter determining which target classes this introduction should apply to.
     *
     * This represents the class part of a pointcut. Note that method matching doesn't make sense to introductions.
     */
    public function getClassFilter(): PointFilter;
}
