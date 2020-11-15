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

class UnsignedIntegerTest extends \PHPUnit_Framework_TestCase {
    public function testUnsignedIntType()
    {
        $type = new UnsignedInteger(1);
        $this->assertEquals(1, $type->get());
    }

    public function testUnsignedIntTypeThrowsExceptionWithNegative()
    {
        $this->setExpectedException('\InvalidArgumentException');
        new UnsignedInteger(-1);
    }

    public function testUnsignedIntTypeThrowsExceptionWithWithString()
    {
        $this->setExpectedException('\InvalidArgumentException');
        new UnsignedInteger('invalid');
    }
}
