<?php

/*
 * This file is part of the Zephir.
 *
 * (c) Phalcon Team <team@zephir-lang.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Extension\Properties;

use PHPUnit\Framework\TestCase;

class StaticPublicPropertiesTest extends TestCase
{
    public function testAssertations()
    {
        $this->assertNull(\Stub\Properties\StaticPublicProperties::$someNull);
        $this->assertNull(\Stub\Properties\StaticPublicProperties::$someNullInitial);
        $this->assertFalse(\Stub\Properties\StaticPublicProperties::$someFalse);
        $this->assertTrue(\Stub\Properties\StaticPublicProperties::$someTrue);
        $this->assertSame(\Stub\Properties\StaticPublicProperties::$someInteger, 10);
        $this->assertSame(\Stub\Properties\StaticPublicProperties::$someDouble, 10.25);
        $this->assertSame(\Stub\Properties\StaticPublicProperties::$someString, 'test');
    }

    public function testIssues1904()
    {
        $value = &\Stub\Properties\StaticPublicProperties::$someString;
        $value = 'test1';
        $this->assertSame(\Stub\Properties\StaticPublicProperties::$someString, $value);
        \Stub\Properties\StaticPublicProperties::$someString = 'test2';
        $this->assertSame(\Stub\Properties\StaticPublicProperties::$someString, $value);
        // Disabled due to:
        // https://github.com/phalcon/zephir/issues/1941#issuecomment-538654340
        // \Stub\Properties\StaticPublicProperties::setSomeString('test3');
        // $this->assertSame(\Stub\Properties\StaticPublicProperties::$someString, $value);
    }

    public function testIssues2020()
    {
        \Stub\Properties\StaticPublicProperties::testAddAndSub();
        $this->assertEquals(1, \Stub\Properties\StaticPublicProperties::$someAdd);
        $this->assertEquals(-1, \Stub\Properties\StaticPublicProperties::$someSub);

        // PHP Notice:  A non well formed numeric value encountered
        //\Stub\Properties\StaticPublicProperties::testAddAndSub2();
        //$this->assertEquals(2, \Stub\Properties\StaticPublicProperties::$someAdd);
        //$this->assertEquals(-2, \Stub\Properties\StaticPublicProperties::$someSub);

        \Stub\Properties\StaticPublicProperties::testAddAndSub3();
        $this->assertEquals(2, \Stub\Properties\StaticPublicProperties::$someAdd);
        $this->assertEquals(-2, \Stub\Properties\StaticPublicProperties::$someSub);

        \Stub\Properties\StaticPublicProperties::testAddAndSub4();
        $this->assertEquals(3, \Stub\Properties\StaticPublicProperties::$someAdd);
        $this->assertEquals(-3, \Stub\Properties\StaticPublicProperties::$someSub);

        \Stub\Properties\StaticPublicProperties::testAddAndSub4();
        $this->assertEquals(4, \Stub\Properties\StaticPublicProperties::$someAdd);
        $this->assertEquals(-4, \Stub\Properties\StaticPublicProperties::$someSub);
    }
}
