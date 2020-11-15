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

class IssetTest extends TestCase
{
    public $b = 'a';
    private $test2 = 'b';

    /** @var \Stub\IssetTest */
    private $test;

    public function setUp()
    {
        $this->test = new \Stub\IssetTest();
    }

    public function testIssetArray()
    {
        $testArray = ['a', 'abc' => 'def', 'gef' => '123'];
        $this->assertTrue($this->test->testIssetArray1($testArray, 'abc'));
        $this->assertTrue(!$this->test->testIssetArray2($testArray, 12));
        $this->assertTrue($this->test->testIssetArray3($testArray, 'gef'));
        $this->assertTrue($this->test->testIssetArray4($testArray));
        $this->assertTrue(!$this->test->testIssetArray5($testArray));
    }

    public function testIssetProperties()
    {
        $this->assertTrue($this->test->testIssetProperty1($this));
        $this->assertTrue($this->test->testIssetProperty2($this, 'test2'));
        $this->assertTrue(!$this->test->testIssetProperty2($this, 'test3'));
        $this->assertTrue($this->test->testIssetProperty3($this));
    }

    public function testIssetDynamicProperty()
    {
        $this->assertTrue($this->test->testIssetDynamicProperty1());
        $this->assertTrue(!$this->test->testIssetDynamicProperty2($this));
        $this->s = ['a' => 'true'];
        $this->assertTrue($this->test->testIssetDynamicProperty2($this));
    }
}
