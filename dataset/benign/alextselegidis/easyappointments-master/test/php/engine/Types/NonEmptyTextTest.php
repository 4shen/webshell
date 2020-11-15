<?php

/* ----------------------------------------------------------------------------
 * Easy!Appointments - Open Source Web Scheduler
 *
 * @package     EasyAppointments
 * @author      A.Tselegidis <alextselegidis@gmail.com>
 * @copyright   Copyright (c) 2013 - 2018, Alex Tselegidis
 * @license     http://opensource.org/licenses/GPL-3.0 - GPLv3
 * @link        http://easyappointments.org
 * @since       v1.2.0
 * ---------------------------------------------------------------------------- */

namespace EA\Engine\Types;

class NonEmptyAlphanumericTest extends \PHPUnit_Framework_TestCase {
    public function testNonEmptyStringType()
    {
        $type = new NonEmptyText('Hello!');
        $this->assertEquals('Hello!', $type->get());
    }

    public function testNonEmptyStringTypeThrowsExceptionWithEmptyString()
    {
        $this->setExpectedException('\InvalidArgumentException');
        new NonEmptyText('');
    }

    public function testNonEmptyStringTypeThrowsExceptionWithInvalidValue()
    {
        $this->setExpectedException('\InvalidArgumentException');
        new NonEmptyText(NULL);
    }
}
