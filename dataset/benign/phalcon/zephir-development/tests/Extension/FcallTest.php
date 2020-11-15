<?php

/*
 * This file is part of the Zephir.
 *
 * (c) Phalcon Team <team@zephir-lang.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Extension;

use PHPUnit\Framework\TestCase;
use Stub\Fcall;
use Stub\Oo\PropertyAccess;

class FcallTest extends TestCase
{
    /** @var Fcall */
    private $test;

    public function setUp()
    {
        $this->test = new Fcall();
    }

    public function testCall()
    {
        $this->assertSame(0, $this->test->testCall1());
        $this->assertGreaterThan(-1, $this->test->testCall2());
        $this->assertSame(2, $this->test->testCall1FromVar());
    }

    public function testStrtok()
    {
        $this->assertFalse($this->test->testStrtokFalse());
        $this->assertSame('test', $this->test->testStrtokVarBySlash('test'));
    }

    /**
     * @dataProvider getArgsDataProvider
     *
     * @param mixed $param1
     * @param mixed $param2
     */
    public function testFunctionGetArgs($param1, $param2)
    {
        $this->assertSame([$param1, $param2], $this->test->testFunctionGetArgs($param1, $param2));
    }

    /**
     * @test
     * @dataProvider getArgsDataProvider
     *
     * @param mixed $param1
     * @param mixed $param2
     */
    public function shouldGetArgsUsingAllExtraParams($param1, $param2)
    {
        $this->assertSame([$param1, $param2], $this->test->testFunctionGetArgsAllExtra($param1, $param2));
    }

    /**
     * @test
     * @dataProvider getArgsDataProvider
     *
     * @param mixed $param1
     * @param mixed $param2
     */
    public function shouldGetArgsUsingAllExtraParamsAndStaticFunction($param1, $param2)
    {
        $this->assertSame([$param1, $param2], Fcall::testStaticFunctionGetArgsAllExtra($param1, $param2));
    }

    public function getArgsDataProvider()
    {
        return [
            [true, false],
            [1025, false],
            [false, 1234],
            [[1, 2, 3], false],
            [true, false],
            [1025, false],
            [false, 1234],
        ];
    }

    /** @test */
    public function shouldGedDesiredArgUsingAllExtraParams()
    {
        $this->assertSame([true, false], $this->test->testFunctionGetArgAllExtra(true, false));
    }

    /** @test */
    public function shouldGedDesiredArgUsingAllExtraParamsAndStaticFunction()
    {
        $this->assertSame([true, false], Fcall::testStaticFunctionGetArgAllExtra(true, false));
    }

    public function testArrayFill()
    {
        $this->assertSame(
            [array_fill(0, 5, '?'), array_fill(0, 6, '?')],
            $this->test->testArrayFill()
        );
    }

    public function testFunctionDeclaration()
    {
        $this->assertSame('aaaaa', \Stub\zephir_namespaced_method_test('a'));
        $this->assertTrue(\Stub\test_call_relative_object_hint(new PropertyAccess()));
        $this->assertTrue(\Stub\test_call_object_hint(new PropertyAccess()));

        $this->assertSame('ab', zephir_global_method_test('ab/c'));

        $this->assertInstanceOf(\stdClass::class, \Stub\zephir_namespaced_method_with_type_casting(new \stdClass()));
        $this->assertInstanceOf(\stdClass::class, zephir_global_method_with_type_casting(new \stdClass()));
    }
}
