<?php
/**
 * tests for PhpMyAdmin\CheckUserPrivileges
 */

declare(strict_types=1);

namespace PhpMyAdmin\Tests;

use PhpMyAdmin\CheckUserPrivileges;

/**
 * tests for PhpMyAdmin\CheckUserPrivileges
 */
class CheckUserPrivilegesTest extends AbstractTestCase
{
    /** @var CheckUserPrivileges */
    private $checkUserPrivileges;

    /**
     * prepares environment for tests
     */
    protected function setUp(): void
    {
        parent::setUp();
        $GLOBALS['server'] = 1;
        $GLOBALS['cfg']['Server']['DisableIS'] = false;
        $GLOBALS['col_priv'] = false;
        $GLOBALS['db_priv'] = false;
        $GLOBALS['proc_priv'] = false;
        $GLOBALS['table_priv'] = false;
        $GLOBALS['is_reload_priv'] = false;

        $this->checkUserPrivileges = new CheckUserPrivileges($GLOBALS['dbi']);
    }

    /**
     * Test for getItemsFromShowGrantsRow
     *
     * @return void
     */
    public function testGetItemsFromShowGrantsRow()
    {
        // TEST CASE 1
        $show_grants_full_row = 'GRANT ALL PRIVILEGES ON *.* '
            . "TO 'root'@'localhost' WITH GRANT OPTION";

        [
            $show_grants_str,
            $show_grants_dbname,
            $show_grants_tblname,
        ] = $this->checkUserPrivileges->getItemsFromShowGrantsRow(
            $show_grants_full_row
        );

        $this->assertEquals(
            'ALL PRIVILEGES',
            $show_grants_str
        );

        $this->assertEquals(
            '*',
            $show_grants_dbname
        );

        $this->assertEquals(
            '*',
            $show_grants_tblname
        );

        // TEST CASE 2
        $show_grants_full_row = 'GRANT ALL PRIVILEGES ON `mysql`.* TO '
            . "'root'@'localhost' WITH GRANT OPTION";

        [
            $show_grants_str,
            $show_grants_dbname,
            $show_grants_tblname,
        ] = $this->checkUserPrivileges->getItemsFromShowGrantsRow(
            $show_grants_full_row
        );

        $this->assertEquals(
            'ALL PRIVILEGES',
            $show_grants_str
        );

        $this->assertEquals(
            'mysql',
            $show_grants_dbname
        );

        $this->assertEquals(
            '*',
            $show_grants_tblname
        );

        // TEST CASE 3
        $show_grants_full_row = 'GRANT SELECT, INSERT, UPDATE, DELETE '
            . "ON `mysql`.`columns_priv` TO 'root'@'localhost'";

        [
            $show_grants_str,
            $show_grants_dbname,
            $show_grants_tblname,
        ] = $this->checkUserPrivileges->getItemsFromShowGrantsRow(
            $show_grants_full_row
        );

        $this->assertEquals(
            'SELECT, INSERT, UPDATE, DELETE',
            $show_grants_str
        );

        $this->assertEquals(
            'mysql',
            $show_grants_dbname
        );

        $this->assertEquals(
            'columns_priv',
            $show_grants_tblname
        );

        // TEST CASE 4
        $show_grants_full_row = 'GRANT ALL PRIVILEGES ON `cptest\_.`.* TO '
            . "'cptest'@'localhost'";

        [
            $show_grants_str,
            $show_grants_dbname,
            $show_grants_tblname,
        ] = $this->checkUserPrivileges->getItemsFromShowGrantsRow(
            $show_grants_full_row
        );

        $this->assertEquals(
            'cptest\_.',
            $show_grants_dbname
        );

        $show_grants_full_row = 'GRANT ALL PRIVILEGES ON `cptest\_.a.b.c.d.e.f.g.h.i.j.k.'
            . 'l.m.n.o.p.q.r.s.t.u.v.w.x.y.z`.* TO ' . "'cptest'@'localhost'";

        [
            $show_grants_str,
            $show_grants_dbname,
            $show_grants_tblname,
        ] = $this->checkUserPrivileges->getItemsFromShowGrantsRow(
            $show_grants_full_row
        );

        $this->assertEquals(
            'cptest\_.a.b.c.d.e.f.g.h.i.j.k.l.m.n.o.p.q.r.s.t.u.v.w.x.y.z',
            $show_grants_dbname
        );
    }

    /**
     * Test for checkRequiredPrivilegesForAdjust
     *
     * @return void
     */
    public function testCheckRequiredPrivilegesForAdjust()
    {
        // TEST CASE 1
        $show_grants_full_row = 'GRANT ALL PRIVILEGES ON *.* '
            . "TO 'root'@'localhost' WITH GRANT OPTION";
        [
            $show_grants_str,
            $show_grants_dbname,
            $show_grants_tblname,
        ] = $this->checkUserPrivileges->getItemsFromShowGrantsRow(
            $show_grants_full_row
        );

        // call the to-be-tested function
        $this->checkUserPrivileges->checkRequiredPrivilegesForAdjust(
            $show_grants_str,
            $show_grants_dbname,
            $show_grants_tblname
        );

        $this->assertTrue(
            $GLOBALS['col_priv']
        );

        $this->assertTrue(
            $GLOBALS['db_priv']
        );

        $this->assertTrue(
            $GLOBALS['proc_priv']
        );

        $this->assertTrue(
            $GLOBALS['table_priv']
        );

        // re-initialise the privileges
        $this->setUp();

        // TEST CASE 2
        $show_grants_full_row = 'GRANT ALL PRIVILEGES ON `mysql`.* TO '
            . "'root'@'localhost' WITH GRANT OPTION";
        [
            $show_grants_str,
            $show_grants_dbname,
            $show_grants_tblname,
        ] = $this->checkUserPrivileges->getItemsFromShowGrantsRow(
            $show_grants_full_row
        );

        // call the to-be-tested function
        $this->checkUserPrivileges->checkRequiredPrivilegesForAdjust(
            $show_grants_str,
            $show_grants_dbname,
            $show_grants_tblname
        );

        $this->assertTrue(
            $GLOBALS['col_priv']
        );

        $this->assertTrue(
            $GLOBALS['db_priv']
        );

        $this->assertTrue(
            $GLOBALS['proc_priv']
        );

        $this->assertTrue(
            $GLOBALS['table_priv']
        );

        // re-initialise the privileges
        $this->setUp();

        // TEST CASE 3
        $show_grants_full_row = 'GRANT SELECT, INSERT, UPDATE, DELETE ON '
            . "`mysql`.* TO 'root'@'localhost'";
        [
            $show_grants_str,
            $show_grants_dbname,
            $show_grants_tblname,
        ] = $this->checkUserPrivileges->getItemsFromShowGrantsRow(
            $show_grants_full_row
        );

        // call the to-be-tested function
        $this->checkUserPrivileges->checkRequiredPrivilegesForAdjust(
            $show_grants_str,
            $show_grants_dbname,
            $show_grants_tblname
        );

        $this->assertTrue(
            $GLOBALS['col_priv']
        );

        $this->assertTrue(
            $GLOBALS['db_priv']
        );

        $this->assertTrue(
            $GLOBALS['proc_priv']
        );

        $this->assertTrue(
            $GLOBALS['table_priv']
        );

        // re-initialise the privileges
        $this->setUp();

        // TEST CASE 4
        $show_grants_full_row = 'GRANT SELECT, INSERT, UPDATE, DELETE ON '
            . "`mysql`.`db` TO 'root'@'localhost'";
        [
            $show_grants_str,
            $show_grants_dbname,
            $show_grants_tblname,
        ] = $this->checkUserPrivileges->getItemsFromShowGrantsRow(
            $show_grants_full_row
        );

        // call the to-be-tested function
        $this->checkUserPrivileges->checkRequiredPrivilegesForAdjust(
            $show_grants_str,
            $show_grants_dbname,
            $show_grants_tblname
        );

        $this->assertFalse(
            $GLOBALS['col_priv']
        );

        $this->assertTrue(
            $GLOBALS['db_priv']
        );

        $this->assertFalse(
            $GLOBALS['proc_priv']
        );

        $this->assertFalse(
            $GLOBALS['table_priv']
        );
    }
}
