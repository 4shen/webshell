<?php
/**
 * Tests for PhpMyAdmin\Plugins\Import\ImportCsv class
 */

declare(strict_types=1);

namespace PhpMyAdmin\Tests\Plugins\Import;

use PhpMyAdmin\DatabaseInterface;
use PhpMyAdmin\File;
use PhpMyAdmin\Plugins\Import\ImportCsv;
use PhpMyAdmin\Tests\AbstractTestCase;
use function basename;

/**
 * Tests for PhpMyAdmin\Plugins\Import\ImportCsv class
 */
class ImportCsvTest extends AbstractTestCase
{
    /**
     * @var ImportCsv
     * @access protected
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @access protected
     */
    protected function setUp(): void
    {
        parent::setUp();
        parent::loadDefaultConfig();
        $GLOBALS['server'] = 0;
        $GLOBALS['plugin_param'] = 'csv';
        $this->object = new ImportCsv();

        unset($GLOBALS['db']);

        //setting
        $GLOBALS['finished'] = false;
        $GLOBALS['read_limit'] = 100000000;
        $GLOBALS['offset'] = 0;
        $GLOBALS['cfg']['Server']['DisableIS'] = false;

        $GLOBALS['import_file'] = 'test/test_data/db_test.csv';
        $GLOBALS['import_text'] = 'ImportCsv_Test';
        $GLOBALS['compression'] = 'none';
        $GLOBALS['read_multiply'] = 10;
        $GLOBALS['import_type'] = 'Xml';
        $GLOBALS['import_handle'] = new File($GLOBALS['import_file']);
        $GLOBALS['import_handle']->open();

        //separator for csv
        $GLOBALS['csv_terminated'] = "\015";
        $GLOBALS['csv_enclosed'] = '"';
        $GLOBALS['csv_escaped'] = '"';
        $GLOBALS['csv_new_line'] = 'auto';
        $GLOBALS['import_file_name'] = basename($GLOBALS['import_file'], '.csv');

        //$_SESSION

        //Mock DBI
        $dbi = $this->getMockBuilder(DatabaseInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $GLOBALS['dbi'] = $dbi;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @access protected
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        unset($this->object);
    }

    /**
     * Test for getProperties
     *
     * @return void
     *
     * @group medium
     */
    public function testGetProperties()
    {
        $properties = $this->object->getProperties();
        $this->assertEquals(
            __('CSV'),
            $properties->getText()
        );
        $this->assertEquals(
            'csv',
            $properties->getExtension()
        );
    }

    /**
     * Test for doImport
     *
     * @return void
     *
     * @group medium
     */
    public function testDoImport()
    {
        //$sql_query_disabled will show the import SQL detail
        global $sql_query, $sql_query_disabled;
        $sql_query_disabled = false;

        //Test function called
        $this->object->doImport();

        //asset that all sql are executed
        $this->assertStringContainsString(
            'CREATE DATABASE IF NOT EXISTS `CSV_DB 1` DEFAULT CHARACTER',
            $sql_query
        );
        $this->assertStringContainsString(
            'CREATE TABLE IF NOT EXISTS `CSV_DB 1`.`' . $GLOBALS['import_file_name'] . '`',
            $sql_query
        );

        $this->assertTrue(
            $GLOBALS['finished']
        );
    }

    /**
     * Test for partial import/setting table and database names in doImport
     *
     * @return void
     *
     * @group medium
     */
    public function testDoPartialImport()
    {
        //$sql_query_disabled will show the import SQL detail
        global $sql_query, $sql_query_disabled;
        $sql_query_disabled = false;

        $GLOBALS['import_file'] = 'test/test_data/db_test_partial_import.csv';
        $_REQUEST['csv_new_tbl_name'] = 'ImportTestTable';
        $_REQUEST['csv_new_db_name'] = 'ImportTestDb';
        $_REQUEST['csv_partial_import'] = 5;

        //Test function called
        $this->object->doImport();

        //asset that all sql are executed
        $this->assertStringContainsString(
            'CREATE DATABASE IF NOT EXISTS `ImportTestDb` DEFAULT CHARACTER',
            $sql_query
        );
        $this->assertStringContainsString(
            'CREATE TABLE IF NOT EXISTS `ImportTestDb`.`ImportTestTable`',
            $sql_query
        );

        $this->assertTrue(
            $GLOBALS['finished']
        );

        unset($_REQUEST['csv_new_tbl_name']);
        unset($_REQUEST['csv_new_db_name']);
        unset($_REQUEST['csv_partial_import']);
    }

    /**
     * Test for getProperties for Table param
     *
     * @return void
     *
     * @group medium
     */
    public function testGetPropertiesForTable()
    {
        $GLOBALS['plugin_param'] = 'table';
        $this->object = new ImportCsv();
        $properties = $this->object->getProperties();
        $this->assertEquals(
            __('CSV'),
            $properties->getText()
        );
        $this->assertEquals(
            'csv',
            $properties->getExtension()
        );
    }

    /**
     * Test for doImport for _getAnalyze = false, should be OK as well
     *
     * @return void
     *
     * @group medium
     */
    public function testDoImportNotAnalysis()
    {
        //$sql_query_disabled will show the import SQL detail
        global $sql_query, $sql_query_disabled;
        $sql_query_disabled = false;

        //Test function called
        $this->object->doImport();

        //asset that all sql are executed
        $this->assertStringContainsString(
            'CREATE DATABASE IF NOT EXISTS `CSV_DB 1` DEFAULT CHARACTER',
            $sql_query
        );

        $this->assertStringContainsString(
            'CREATE TABLE IF NOT EXISTS `CSV_DB 1`.`' . $GLOBALS['import_file_name'] . '`',
            $sql_query
        );

        $this->assertTrue(
            $GLOBALS['finished']
        );
    }
}
