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

class ArrayAccessTest extends TestCase
{
    public function testTest()
    {
        $arrtest = new \Stub\ArrayAccessTest();

        $this->assertTrue($arrtest->exits('one'));
        $this->assertSame(2, $arrtest->get());
    }
}
