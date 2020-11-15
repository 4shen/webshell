<?php

/*
 * This file is part of the Zephir.
 *
 * (c) Phalcon Team <team@zephir-lang.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Extension\Globals;

use PHPUnit\Framework\TestCase;
use Stub\Globals\Post;

class PostTest extends TestCase
{
    /**
     * @test
     * @issue https://github.com/phalcon/zephir/issues/1623
     */
    public function shouldNotTriggerAnyErrorIfPostIsUndefined()
    {
        $tester = new Post();

        unset($_POST);

        $this->assertFalse(isset($_POST));
        $this->assertFalse($tester->hasValue('issue-1623'));
    }

    /**
     * @test
     * @issue https://github.com/phalcon/zephir/issues/1623
     */
    public function shouldReturnFalseIfVariableIsUndefined()
    {
        $tester = new Post();

        $_POST = [];

        $this->assertTrue(isset($_POST));
        $this->assertFalse($tester->hasValue('issue-1623'));
    }

    /**
     * @test
     * @issue https://github.com/phalcon/zephir/issues/1623
     */
    public function shouldReturnTrueIfVariableIsDefined()
    {
        $tester = new Post();

        $_POST = ['issue-1623' => true];

        $this->assertTrue(isset($_POST));
        $this->assertTrue($tester->hasValue('issue-1623'));
    }
}
