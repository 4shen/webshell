<?php
/**
 * Test for faked database access
 */

declare(strict_types=1);

namespace PhpMyAdmin\Tests;

use PhpMyAdmin\Database\DatabaseList;
use PhpMyAdmin\DatabaseInterface;
use PhpMyAdmin\Query\Utilities;
use PhpMyAdmin\SystemDatabase;
use PhpMyAdmin\Tests\Stubs\DbiDummy;
use PhpMyAdmin\Util;
use stdClass;

/**
 * Tests basic functionality of dummy dbi driver
 */
class DatabaseInterfaceTest extends AbstractTestCase
{
    /** @var DatabaseInterface */
    private $_dbi;

    /**
     * Configures test parameters.
     */
    protected function setUp(): void
    {
        parent::setUp();
        parent::loadDefaultConfig();
        parent::defineVersionConstants();
        $GLOBALS['server'] = 0;
        $extension = new DbiDummy();
        $this->_dbi = new DatabaseInterface($extension);
    }

    /**
     * Tests for DBI::getCurrentUser() method.
     *
     * @param array  $value    value
     * @param string $string   string
     * @param array  $expected expected result
     *
     * @test
     * @dataProvider currentUserData
     */
    public function testGetCurrentUser($value, $string, $expected): void
    {
        Util::cacheUnset('mysql_cur_user');

        $extension = new DbiDummy();
        $extension->setResult('SELECT CURRENT_USER();', $value);

        $dbi = new DatabaseInterface($extension);

        $this->assertEquals(
            $expected,
            $dbi->getCurrentUserAndHost()
        );

        $this->assertEquals(
            $string,
            $dbi->getCurrentUser()
        );
    }

    /**
     * Data provider for getCurrentUser() tests.
     *
     * @return array
     */
    public function currentUserData()
    {
        return [
            [
                [['pma@localhost']],
                'pma@localhost',
                [
                    'pma',
                    'localhost',
                ],
            ],
            [
                [['@localhost']],
                '@localhost',
                [
                    '',
                    'localhost',
                ],
            ],
            [
                false,
                '@',
                [
                    '',
                    '',
                ],
            ],
        ];
    }

    /**
     * Tests for DBI::getColumnMapFromSql() method.
     *
     * @return void
     *
     * @test
     */
    public function testPMAGetColumnMap()
    {
        $extension = $this->getMockBuilder(DbiDummy::class)
            ->disableOriginalConstructor()
            ->getMock();

        $extension->expects($this->any())
            ->method('realQuery')
            ->will($this->returnValue(true));

        $meta1 = new stdClass();
        $meta1->table = 'meta1_table';
        $meta1->name = 'meta1_name';

        $meta2 = new stdClass();
        $meta2->table = 'meta2_table';
        $meta2->name = 'meta2_name';

        $extension->expects($this->any())
            ->method('getFieldsMeta')
            ->will(
                $this->returnValue(
                    [
                        $meta1,
                        $meta2,
                    ]
                )
            );

        $dbi = new DatabaseInterface($extension);

        $sql_query = 'PMA_sql_query';
        $view_columns = [
            'view_columns1',
            'view_columns2',
        ];

        $column_map = $dbi->getColumnMapFromSql(
            $sql_query,
            $view_columns
        );

        $this->assertEquals(
            [
                'table_name' => 'meta1_table',
                'refering_column' => 'meta1_name',
                'real_column' => 'view_columns1',
            ],
            $column_map[0]
        );
        $this->assertEquals(
            [
                'table_name' => 'meta2_table',
                'refering_column' => 'meta2_name',
                'real_column' => 'view_columns2',
            ],
            $column_map[1]
        );
    }

    /**
     * Tests for DBI::getSystemDatabase() method.
     *
     * @return void
     *
     * @test
     */
    public function testGetSystemDatabase()
    {
        $sd = $this->_dbi->getSystemDatabase();
        $this->assertInstanceOf(SystemDatabase::class, $sd);
    }

    /**
     * Tests for DBI::postConnectControl() method.
     *
     * @return void
     *
     * @test
     */
    public function testPostConnectControl()
    {
        $GLOBALS['db'] = '';
        $GLOBALS['cfg']['Server']['only_db'] = [];
        $this->_dbi->postConnectControl();
        $this->assertInstanceOf(DatabaseList::class, $GLOBALS['dblist']);
    }

    /**
     * Test for getDbCollation
     *
     * @return void
     *
     * @test
     */
    public function testGetDbCollation()
    {
        $GLOBALS['server'] = 1;
        // test case for system schema
        $this->assertEquals(
            'utf8_general_ci',
            $this->_dbi->getDbCollation('information_schema')
        );

        $GLOBALS['cfg']['Server']['DisableIS'] = false;
        $GLOBALS['cfg']['DBG']['sql'] = false;

        $this->assertEquals(
            'utf8_general_ci',
            $this->_dbi->getDbCollation('pma_test')
        );
    }

    /**
     * Test for getServerCollation
     *
     * @return void
     *
     * @test
     */
    public function testGetServerCollation()
    {
        $GLOBALS['server'] = 1;
        $GLOBALS['cfg']['DBG']['sql'] = true;
        $this->assertEquals('utf8_general_ci', $this->_dbi->getServerCollation());
    }

    /**
     * Test error formatting
     *
     * @param int    $error_number  Error code
     * @param string $error_message Error message as returned by server
     * @param string $match         Expected text
     *
     * @dataProvider errorData
     */
    public function testFormatError($error_number, $error_message, $match): void
    {
        $this->assertStringContainsString(
            $match,
            Utilities::formatError($error_number, $error_message)
        );
    }

    /**
     * @return array
     */
    public function errorData()
    {
        return [
            [
                2002,
                'msg',
                'The server is not responding',
            ],
            [
                2003,
                'msg',
                'The server is not responding',
            ],
            [
                1698,
                'msg',
                'index.php?route=/logout',
            ],
            [
                1005,
                'msg',
                'index.php?route=/server/engines',
            ],
            [
                1005,
                'errno: 13',
                'Please check privileges',
            ],
            [
                -1,
                'error message',
                'error message',
            ],
        ];
    }

    /**
     * Tests for DBI::isAmazonRds() method.
     *
     * @param mixed $value    value
     * @param mixed $expected expected result
     *
     * @return void
     *
     * @test
     * @dataProvider isAmazonRdsData
     */
    public function atestIsAmazonRdsData($value, $expected)
    {
        Util::cacheUnset('is_amazon_rds');

        $extension = new DbiDummy();
        $extension->setResult('SELECT @@basedir', $value);

        $dbi = new DatabaseInterface($extension);

        $this->assertEquals(
            $expected,
            $dbi->isAmazonRds()
        );
    }

    /**
     * Data provider for isAmazonRds() tests.
     *
     * @return array
     */
    public function isAmazonRdsData()
    {
        return [
            [
                [['/usr']],
                false,
            ],
            [
                [['E:/mysql']],
                false,
            ],
            [
                [['/rdsdbbin/mysql/']],
                true,
            ],
            [
                [['/rdsdbbin/mysql-5.7.18/']],
                true,
            ],
        ];
    }

    /**
     * Test for version parsing
     *
     * @param string $version  version to parse
     * @param int    $expected expected numeric version
     * @param int    $major    expected major version
     * @param bool   $upgrade  whether upgrade should ne needed
     *
     * @dataProvider versionData
     */
    public function testVersion($version, $expected, $major, $upgrade): void
    {
        $ver_int = Utilities::versionToInt($version);
        $this->assertEquals($expected, $ver_int);
        $this->assertEquals($major, (int) ($ver_int / 10000));
        $this->assertEquals($upgrade, $ver_int < $GLOBALS['cfg']['MysqlMinVersion']['internal']);
    }

    /**
     * @return array
     */
    public function versionData()
    {
        return [
            [
                '5.0.5',
                50005,
                5,
                true,
            ],
            [
                '5.05.01',
                50501,
                5,
                false,
            ],
            [
                '5.6.35',
                50635,
                5,
                false,
            ],
            [
                '10.1.22-MariaDB-',
                100122,
                10,
                false,
            ],
        ];
    }

    /**
     * Tests for DBI::setCollationl() method.
     *
     * @return void
     *
     * @test
     */
    public function testSetCollation()
    {
        $extension = $this->getMockBuilder(DbiDummy::class)
            ->disableOriginalConstructor()
            ->getMock();
        $extension->expects($this->any())->method('escapeString')
            ->will($this->returnArgument(1));

        $extension->expects($this->exactly(4))
            ->method('realQuery')
            ->withConsecutive(
                ["SET collation_connection = 'utf8_czech_ci';"],
                ["SET collation_connection = 'utf8mb4_bin_ci';"],
                ["SET collation_connection = 'utf8_czech_ci';"],
                ["SET collation_connection = 'utf8_bin_ci';"]
            )
            ->willReturnOnConsecutiveCalls(
                true,
                true,
                true,
                true
            );

        $dbi = new DatabaseInterface($extension);

        $GLOBALS['charset_connection'] = 'utf8mb4';
        $dbi->setCollation('utf8_czech_ci');
        $dbi->setCollation('utf8mb4_bin_ci');
        $GLOBALS['charset_connection'] = 'utf8';
        $dbi->setCollation('utf8_czech_ci');
        $dbi->setCollation('utf8mb4_bin_ci');
    }

    /**
     * Tests for DBI::getForeignKeyConstrains() method.
     *
     * @return void
     *
     * @test
     */
    public function testGetForeignKeyConstrains()
    {
        $this->assertEquals([
            [
                'TABLE_NAME' => 'table2',
                'COLUMN_NAME' => 'idtable2',
                'REFERENCED_TABLE_NAME' => 'table1',
                'REFERENCED_COLUMN_NAME' => 'idtable1',
            ],
        ], $this->_dbi->getForeignKeyConstrains('test', ['table1', 'table2']));
    }
}
