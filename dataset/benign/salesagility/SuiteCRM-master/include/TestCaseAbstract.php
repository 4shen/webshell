<?php
/**
 *
 * SugarCRM Community Edition is a customer relationship management program developed by
 * SugarCRM, Inc. Copyright (C) 2004-2013 SugarCRM Inc.
 *
 * SuiteCRM is an extension to SugarCRM Community Edition developed by SalesAgility Ltd.
 * Copyright (C) 2011 - 2019 SalesAgility Ltd.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY SUGARCRM, SUGARCRM DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 *
 * You can contact SugarCRM, Inc. headquarters at 10050 North Wolfe Road,
 * SW2-130, Cupertino, CA 95014, USA. or at email address contact@sugarcrm.com.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * SugarCRM" logo and "Supercharged by SuiteCRM" logo. If the display of the logos is not
 * reasonably feasible for technical reasons, the Appropriate Legal Notices must
 * display the words "Powered by SugarCRM" and "Supercharged by SuiteCRM".
 */

namespace SuiteCRM;

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

use PHPUnit_Framework_TestCase;
use SuiteCRM\Exception\Exception;

/**
 * Class TestCaseAbstract
 * @package SuiteCRM
 */
abstract class TestCaseAbstract extends PHPUnit_Framework_TestCase
{
    use DatabaseTransactions;
    use RefreshDatabase;

    protected static $verbose = true;
    protected static $cleanupStrategy = 'transaction';

    /**
     * @throws Exception
     */
    protected function setUp()
    {
        if (self::$verbose) {
            $currentTestName = get_class($this) . '::' . $this->getName(false);
            fwrite(STDOUT, "\t" . $currentTestName . ' ..');
            for ($i = 60, $iMax = strlen($currentTestName); $i > $iMax; $i--) {
                fwrite(STDOUT, '.');
            }
        }

        if (self::$cleanupStrategy === 'transaction') {
            $this->startDBTransaction();
        } elseif (self::$cleanupStrategy === 'refresh') {
            $this->refreshDatabase();
        } else {
            throw new Exception('Failed to cleanup database, invalid cleanup strategy specified.');
        }
        parent::setUp();
    }

    protected function tearDown()
    {
        parent::tearDown();

        if (self::$cleanupStrategy === 'transaction') {
            $this->rollbackDBTransaction();
        }

        if (self::$verbose) {
            fwrite(STDOUT, " [done]\n");
        }
    }
}
