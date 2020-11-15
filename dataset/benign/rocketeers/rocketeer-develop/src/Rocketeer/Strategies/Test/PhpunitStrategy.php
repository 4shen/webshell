<?php

/*
 * This file is part of Rocketeer
 *
 * (c) Maxime Fabre <ehtnam6@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Rocketeer\Strategies\Test;

use Rocketeer\Strategies\AbstractStrategy;

/**
 * Test an application with PHPUnit.
 */
class PhpunitStrategy extends AbstractStrategy implements TestStrategyInterface
{
    /**
     * @var string
     */
    protected $description = 'Test an application with PHPUnit';

    /**
     * Whether this particular strategy is runnable or not.
     *
     * @return bool
     */
    public function isExecutable()
    {
        return (bool) $this->phpunit()->getBinary();
    }

    /**
     * Run the task.
     *
     * @return bool
     */
    public function test()
    {
        // Run PHPUnit
        $arguments = ['--stop-on-failure' => null];
        $output = $this->runForApplication([
            $this->phpunit()->getCommand(null, [], $arguments),
        ]);

        $status = $this->displayStatusMessage('Tests failed', $output, 'Tests passed successfully');
        if (!$status) {
            $this->explainer->error('Tests failed');
        }

        return $status;
    }
}
