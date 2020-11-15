<?php
/**
 * Tests for PhpMyAdmin\Sql
 */

declare(strict_types=1);

namespace PhpMyAdmin\Tests;

use PhpMyAdmin\Sql;
use stdClass;

/**
 * Tests for PhpMyAdmin\Sql
 */
class SqlTest extends AbstractTestCase
{
    /** @var Sql */
    private $sql;

    /**
     * Setup for test cases
     */
    protected function setUp(): void
    {
        parent::setUp();
        parent::defineVersionConstants();
        parent::setLanguage();
        parent::loadDefaultConfig();
        $GLOBALS['server'] = 1;
        $GLOBALS['db'] = 'db';
        $GLOBALS['table'] = 'table';
        $GLOBALS['cfg']['AllowThirdPartyFraming'] = false;
        $GLOBALS['cfg']['SendErrorReports'] = 'ask';
        $GLOBALS['cfg']['ServerDefault'] = 1;
        $GLOBALS['cfg']['DefaultTabDatabase'] = 'structure';
        $GLOBALS['cfg']['DefaultTabTable'] = 'browse';
        $GLOBALS['cfg']['ShowDatabasesNavigationAsTree'] = true;
        $GLOBALS['cfg']['NavigationTreeDefaultTabTable'] = 'structure';
        $GLOBALS['cfg']['NavigationTreeDefaultTabTable2'] = '';
        $GLOBALS['cfg']['LimitChars'] = 50;
        $GLOBALS['cfg']['Confirm'] = true;
        $GLOBALS['cfg']['LoginCookieValidity'] = 1440;
        $GLOBALS['cfg']['enable_drag_drop_import'] = true;
        $GLOBALS['PMA_PHP_SELF'] = 'index.php';

        $this->sql = new Sql();
    }

    /**
     * Test for getSqlWithLimitClause
     *
     * @return void
     */
    public function testGetSqlWithLimitClause()
    {
        // Test environment.
        $GLOBALS['_SESSION']['tmpval']['pos'] = 1;
        $GLOBALS['_SESSION']['tmpval']['max_rows'] = 2;

        $analyzed_sql_results = $this->sql->parseAndAnalyze(
            'SELECT * FROM test LIMIT 0, 10'
        );
        $this->assertEquals(
            'SELECT * FROM test LIMIT 1, 2 ',
            $this->callFunction($this->sql, Sql::class, 'getSqlWithLimitClause', [&$analyzed_sql_results])
        );
    }

    /**
     * Test for isRememberSortingOrder
     *
     * @return void
     */
    public function testIsRememberSortingOrder()
    {
        // Test environment.
        $GLOBALS['cfg']['RememberSorting'] = true;

        $this->assertTrue(
            $this->callFunction($this->sql, Sql::class, 'isRememberSortingOrder', [
                $this->sql->parseAndAnalyze('SELECT * FROM tbl'),
            ])
        );

        $this->assertFalse(
            $this->callFunction($this->sql, Sql::class, 'isRememberSortingOrder', [
                $this->sql->parseAndAnalyze('SELECT col FROM tbl'),
            ])
        );

        $this->assertFalse(
            $this->callFunction($this->sql, Sql::class, 'isRememberSortingOrder', [
                $this->sql->parseAndAnalyze('SELECT 1'),
            ])
        );

        $this->assertFalse(
            $this->callFunction($this->sql, Sql::class, 'isRememberSortingOrder', [
                $this->sql->parseAndAnalyze('SELECT col1, col2 FROM tbl'),
            ])
        );

        $this->assertFalse(
            $this->callFunction($this->sql, Sql::class, 'isRememberSortingOrder', [
                $this->sql->parseAndAnalyze('SELECT COUNT(*) from tbl'),
            ])
        );
    }

    /**
     * Test for isAppendLimitClause
     *
     * @return void
     */
    public function testIsAppendLimitClause()
    {
        // Test environment.
        $GLOBALS['_SESSION']['tmpval']['max_rows'] = 10;

        $this->assertTrue(
            $this->callFunction($this->sql, Sql::class, 'isAppendLimitClause', [
                $this->sql->parseAndAnalyze('SELECT * FROM tbl'),
            ])
        );

        $this->assertFalse(
            $this->callFunction($this->sql, Sql::class, 'isAppendLimitClause', [
                $this->sql->parseAndAnalyze('SELECT * from tbl LIMIT 0, 10'),
            ])
        );
    }

    /**
     * Test for isJustBrowsing
     *
     * @return void
     */
    public function testIsJustBrowsing()
    {
        // Test environment.
        $GLOBALS['_SESSION']['tmpval']['max_rows'] = 10;

        $this->assertTrue(
            $this->sql->isJustBrowsing(
                $this->sql->parseAndAnalyze('SELECT * FROM db.tbl'),
                null
            )
        );

        $this->assertTrue(
            $this->sql->isJustBrowsing(
                $this->sql->parseAndAnalyze('SELECT * FROM tbl WHERE 1'),
                null
            )
        );

        $this->assertFalse(
            $this->sql->isJustBrowsing(
                $this->sql->parseAndAnalyze('SELECT * from tbl1, tbl2 LIMIT 0, 10'),
                null
            )
        );
    }

    /**
     * Test for isDeleteTransformationInfo
     *
     * @return void
     */
    public function testIsDeleteTransformationInfo()
    {
        $this->assertTrue(
            $this->callFunction($this->sql, Sql::class, 'isDeleteTransformationInfo', [
                $this->sql->parseAndAnalyze('ALTER TABLE tbl DROP COLUMN col'),
            ])
        );

        $this->assertTrue(
            $this->callFunction($this->sql, Sql::class, 'isDeleteTransformationInfo', [
                $this->sql->parseAndAnalyze('DROP TABLE tbl'),
            ])
        );

        $this->assertFalse(
            $this->callFunction($this->sql, Sql::class, 'isDeleteTransformationInfo', [
                $this->sql->parseAndAnalyze('SELECT * from tbl'),
            ])
        );
    }

    /**
     * Test for hasNoRightsToDropDatabase
     *
     * @return void
     */
    public function testHasNoRightsToDropDatabase()
    {
        $this->assertTrue(
            $this->sql->hasNoRightsToDropDatabase(
                $this->sql->parseAndAnalyze('DROP DATABASE db'),
                false,
                false
            )
        );

        $this->assertFalse(
            $this->sql->hasNoRightsToDropDatabase(
                $this->sql->parseAndAnalyze('DROP TABLE tbl'),
                false,
                false
            )
        );

        $this->assertFalse(
            $this->sql->hasNoRightsToDropDatabase(
                $this->sql->parseAndAnalyze('SELECT * from tbl'),
                false,
                false
            )
        );
    }

    /**
     * Should return false if all columns are not from the same table
     *
     * @return void
     */
    public function testWithMultipleTables()
    {
        $col1 = new stdClass();
        $col1->table = 'table1';
        $col2 = new stdClass();
        $col2->table = 'table1';
        $col3 = new stdClass();
        $col3->table = 'table3';

        $fields_meta = [
            $col1,
            $col2,
            $col3,
        ];
        $this->assertFalse(
            $this->callFunction($this->sql, Sql::class, 'resultSetHasJustOneTable', [$fields_meta])
        );

        // should not matter on where the odd column occurs
        $fields_meta = [
            $col2,
            $col3,
            $col1,
        ];
        $this->assertFalse(
            $this->callFunction($this->sql, Sql::class, 'resultSetHasJustOneTable', [$fields_meta])
        );

        $fields_meta = [
            $col3,
            $col1,
            $col2,
        ];
        $this->assertFalse(
            $this->callFunction($this->sql, Sql::class, 'resultSetHasJustOneTable', [$fields_meta])
        );
    }

    /**
     * Should return true if all the columns are from the same table
     *
     * @return void
     */
    public function testWithSameTable()
    {
        $col1 = new stdClass();
        $col1->table = 'table1';
        $col2 = new stdClass();
        $col2->table = 'table1';
        $col3 = new stdClass();
        $col3->table = 'table1';
        $fields_meta = [
            $col1,
            $col2,
            $col3,
        ];

        $this->assertTrue(
            $this->callFunction($this->sql, Sql::class, 'resultSetHasJustOneTable', [$fields_meta])
        );
    }

    /**
     * Should return true even if function columns (table is '') occur when others
     * are from the same table.
     *
     * @return void
     */
    public function testWithFunctionColumns()
    {
        $col1 = new stdClass();
        $col1->table = 'table1';
        $col2 = new stdClass();
        $col2->table = '';
        $col3 = new stdClass();
        $col3->table = 'table1';

        $fields_meta = [
            $col1,
            $col2,
            $col3,
        ];
        $this->assertTrue(
            $this->callFunction($this->sql, Sql::class, 'resultSetHasJustOneTable', [$fields_meta])
        );

        // should not matter on where the function column occurs
        $fields_meta = [
            $col2,
            $col3,
            $col1,
        ];
        $this->assertTrue(
            $this->callFunction($this->sql, Sql::class, 'resultSetHasJustOneTable', [$fields_meta])
        );

        $fields_meta = [
            $col3,
            $col1,
            $col2,
        ];
        $this->assertTrue(
            $this->callFunction($this->sql, Sql::class, 'resultSetHasJustOneTable', [$fields_meta])
        );
    }

    /**
     * We can not say all the columns are from the same table if all the columns
     * are funtion columns (table is '')
     *
     * @return void
     */
    public function testWithOnlyFunctionColumns()
    {
        $col1 = new stdClass();
        $col1->table = '';
        $col2 = new stdClass();
        $col2->table = '';
        $col3 = new stdClass();
        $col3->table = '';
        $fields_meta = [
            $col1,
            $col2,
            $col3,
        ];

        $this->assertFalse(
            $this->callFunction($this->sql, Sql::class, 'resultSetHasJustOneTable', [$fields_meta])
        );
    }
}
