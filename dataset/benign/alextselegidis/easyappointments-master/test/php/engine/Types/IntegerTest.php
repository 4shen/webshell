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

class IntegerTest extends \PHPUnit_Framework_TestCase {
    public function testIntType()
    {
        $type = new Integer(1);
        $this->assertEquals(1, $type->get());
    }

    public function testIntTypeThrowsExceptionWithFloat()
    {
        $this->setExpectedException('\InvalidArgumentException');
        new Integer(100.00);
    }

    public function testIntTypeThrowsExceptionWithWithString()
    {
        $this->setExpectedException('\InvalidArgumentException');
        new Integer('invalid');
    }
}
