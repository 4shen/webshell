<?php

/*
 * This file is part of the Zephir.
 *
 * (c) Phalcon Team <team@zephir-lang.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Extension;

use PHPUnit\Framework\TestCase;

class DeclareTest extends TestCase
{
    public function testDeclareMcallExpression()
    {
        $test = new \Stub\DeclareTest();
        $this->assertSame($test->testDeclareMcallExpression(), 'hello');
    }
}
