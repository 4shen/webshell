<?php
/**
 * Test for PhpMyAdmin\Import
 */

declare(strict_types=1);

namespace PhpMyAdmin\Tests;

use PhpMyAdmin\DatabaseInterface;
use PhpMyAdmin\Html\Generator;
use PhpMyAdmin\Import;
use PhpMyAdmin\SqlParser\Parser;
use PhpMyAdmin\Url;
use const PHP_INT_MAX;
use function implode;
use function sprintf;
use function time;

/**
 * Tests for import functions
 */
class ImportTest extends AbstractTestCase
{
    /** @var Import $import */
    private $import;

    /**
     * Prepares environment for the test.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $GLOBALS['server'] = 0;
        $GLOBALS['cfg']['ServerDefault'] = '';
        $this->import = new Import();
    }

    /**
     * Test for checkTimeout
     *
     * @return void
     */
    public function testCheckTimeout()
    {
        global $timestamp, $maximum_time, $timeout_passed;

        //Reinit values.
        $timestamp = time();
        $maximum_time = 0;
        $timeout_passed = false;

        $this->assertFalse($this->import->checkTimeout());

        //Reinit values.
        $timestamp = time();
        $maximum_time = 0;
        $timeout_passed = true;

        $this->assertFalse($this->import->checkTimeout());

        //Reinit values.
        $timestamp = time();
        $maximum_time = 30;
        $timeout_passed = true;

        $this->assertTrue($this->import->checkTimeout());

        //Reinit values.
        $timestamp = time() - 15;
        $maximum_time = 30;
        $timeout_passed = false;

        $this->assertFalse($this->import->checkTimeout());

        //Reinit values.
        $timestamp = time() - 60;
        $maximum_time = 30;
        $timeout_passed = false;

        $this->assertTrue($this->import->checkTimeout());
    }

    /**
     * Test for lookForUse
     *
     * @return void
     */
    public function testLookForUse()
    {
        $this->assertEquals(
            [
                null,
                null,
            ],
            $this->import->lookForUse(null, null, null)
        );

        $this->assertEquals(
            [
                'myDb',
                null,
            ],
            $this->import->lookForUse(null, 'myDb', null)
        );

        $this->assertEquals(
            [
                'myDb',
                true,
            ],
            $this->import->lookForUse(null, 'myDb', true)
        );

        $this->assertEquals(
            [
                'myDb',
                true,
            ],
            $this->import->lookForUse('select 1 from myTable', 'myDb', true)
        );

        $this->assertEquals(
            [
                'anotherDb',
                true,
            ],
            $this->import->lookForUse('use anotherDb', 'myDb', false)
        );

        $this->assertEquals(
            [
                'anotherDb',
                true,
            ],
            $this->import->lookForUse('use anotherDb', 'myDb', true)
        );

        $this->assertEquals(
            [
                'anotherDb',
                true,
            ],
            $this->import->lookForUse('use `anotherDb`;', 'myDb', true)
        );
    }

    /**
     * Test for getColumnAlphaName
     *
     * @param string $expected Expected result of the function
     * @param int    $num      The column number
     *
     * @dataProvider provGetColumnAlphaName
     */
    public function testGetColumnAlphaName($expected, $num): void
    {
        $this->assertEquals($expected, $this->import->getColumnAlphaName($num));
    }

    /**
     * Data provider for testGetColumnAlphaName
     *
     * @return array
     */
    public function provGetColumnAlphaName()
    {
        return [
            [
                'A',
                1,
            ],
            [
                'Z',
                0,
            ],
            [
                'AA',
                27,
            ],
            [
                'AZ',
                52,
            ],
            [
                'BA',
                53,
            ],
            [
                'BB',
                54,
            ],
        ];
    }

    /**
     * Test for getColumnNumberFromName
     *
     * @param int         $expected Expected result of the function
     * @param string|null $name     column name(i.e. "A", or "BC", etc.)
     *
     * @dataProvider provGetColumnNumberFromName
     */
    public function testGetColumnNumberFromName($expected, $name): void
    {
        $this->assertEquals($expected, $this->import->getColumnNumberFromName($name));
    }

    /**
     * Data provider for testGetColumnNumberFromName
     *
     * @return array
     */
    public function provGetColumnNumberFromName()
    {
        return [
            [
                1,
                'A',
            ],
            [
                26,
                'Z',
            ],
            [
                27,
                'AA',
            ],
            [
                52,
                'AZ',
            ],
            [
                53,
                'BA',
            ],
            [
                54,
                'BB',
            ],
        ];
    }

    /**
     * Test for getDecimalPrecision
     *
     * @param int         $expected Expected result of the function
     * @param string|null $size     Size of field
     *
     * @dataProvider provGetDecimalPrecision
     */
    public function testGetDecimalPrecision($expected, $size): void
    {
        $this->assertEquals($expected, $this->import->getDecimalPrecision($size));
    }

    /**
     * Data provider for testGetDecimalPrecision
     *
     * @return array
     */
    public function provGetDecimalPrecision()
    {
        return [
            [
                2,
                '2,1',
            ],
            [
                6,
                '6,2',
            ],
            [
                6,
                '6,0',
            ],
            [
                16,
                '16,2',
            ],
        ];
    }

    /**
     * Test for getDecimalScale
     *
     * @param int         $expected Expected result of the function
     * @param string|null $size     Size of field
     *
     * @dataProvider provGetDecimalScale
     */
    public function testGetDecimalScale($expected, $size): void
    {
        $this->assertEquals($expected, $this->import->getDecimalScale($size));
    }

    /**
     * Data provider for testGetDecimalScale
     *
     * @return array
     */
    public function provGetDecimalScale()
    {
        return [
            [
                1,
                '2,1',
            ],
            [
                2,
                '6,2',
            ],
            [
                0,
                '6,0',
            ],
            [
                20,
                '30,20',
            ],
        ];
    }

    /**
     * Test for getDecimalSize
     *
     * @param array       $expected Expected result of the function
     * @param string|null $cell     Cell content
     *
     * @dataProvider provGetDecimalSize
     */
    public function testGetDecimalSize($expected, $cell): void
    {
        $this->assertEquals($expected, $this->import->getDecimalSize($cell));
    }

    /**
     * Data provider for testGetDecimalSize
     *
     * @return array
     */
    public function provGetDecimalSize()
    {
        return [
            [
                [
                    2,
                    1,
                    '2,1',
                ],
                '2.1',
            ],
            [
                [
                    2,
                    1,
                    '2,1',
                ],
                '6.2',
            ],
            [
                [
                    3,
                    1,
                    '3,1',
                ],
                '10.0',
            ],
            [
                [
                    4,
                    2,
                    '4,2',
                ],
                '30.20',
            ],
        ];
    }

    /**
     * Test for detectType
     *
     * @param int         $expected Expected result of the function
     * @param int|null    $type     Last cumulative column type (VARCHAR or INT or
     *                              BIGINT or DECIMAL or NONE)
     * @param string|null $cell     String representation of the cell for which a
     *                              best-fit type is to be determined
     *
     * @dataProvider provDetectType
     */
    public function testDetectType($expected, $type, $cell): void
    {
        $this->assertEquals($expected, $this->import->detectType($type, $cell));
    }

    /**
     * Data provider for testDetectType
     *
     * @return array
     */
    public function provDetectType()
    {
        $data = [
            [
                Import::NONE,
                null,
                'NULL',
            ],
            [
                Import::NONE,
                Import::NONE,
                'NULL',
            ],
            [
                Import::INT,
                Import::INT,
                'NULL',
            ],
            [
                Import::VARCHAR,
                Import::VARCHAR,
                'NULL',
            ],
            [
                Import::VARCHAR,
                null,
                null,
            ],
            [
                Import::VARCHAR,
                Import::INT,
                null,
            ],
            [
                Import::INT,
                Import::INT,
                '10',
            ],
            [
                Import::DECIMAL,
                Import::DECIMAL,
                '10.2',
            ],
            [
                Import::DECIMAL,
                Import::INT,
                '10.2',
            ],
            [
                Import::VARCHAR,
                Import::VARCHAR,
                'test',
            ],
            [
                Import::VARCHAR,
                Import::INT,
                'test',
            ],
        ];

        if (PHP_INT_MAX > 2147483647) {
            $data[] = [
                Import::BIGINT,
                Import::BIGINT,
                '2147483648',
            ];
            $data[] = [
                Import::BIGINT,
                Import::INT,
                '2147483648',
            ];
        } else {
            // To be fixed ?
            // Can not detect a BIGINT since the value is over PHP_INT_MAX
            $data[] = [
                Import::VARCHAR,
                Import::BIGINT,
                '2147483648',
            ];
            $data[] = [
                Import::VARCHAR,
                Import::INT,
                '2147483648',
            ];
        }

        return $data;
    }

    /**
     * Test for getMatchedRows.
     *
     * @return void
     */
    public function testPMAGetMatchedRows()
    {
        $GLOBALS['db'] = 'PMA';
        //mock DBI
        $dbi = $this->getMockBuilder(DatabaseInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $updateQuery = 'UPDATE `table_1` '
            . 'SET `id` = 20 '
            . 'WHERE `id` > 10';
        $simulatedUpdateQuery = 'SELECT `id` FROM `table_1` WHERE `id` > 10 AND (`id` <> 20)';

        $deleteQuery = 'DELETE FROM `table_1` '
            . 'WHERE `id` > 10';
        $simulatedDeleteQuery = 'SELECT * FROM `table_1` WHERE `id` > 10';

        $dbi->expects($this->any())
            ->method('numRows')
            ->with([])
            ->will($this->returnValue(2));

        $dbi->expects($this->any())
            ->method('selectDb')
            ->with('PMA')
            ->will($this->returnValue(true));

        $dbi->expects($this->at(1))
            ->method('tryQuery')
            ->with($simulatedUpdateQuery)
            ->will($this->returnValue([]));

        $dbi->expects($this->at(4))
            ->method('tryQuery')
            ->with($simulatedDeleteQuery)
            ->will($this->returnValue([]));

        $GLOBALS['dbi'] = $dbi;

        $this->simulatedQueryTest($updateQuery, $simulatedUpdateQuery);
        $this->simulatedQueryTest($deleteQuery, $simulatedDeleteQuery);
    }

    /**
     * Tests simulated UPDATE/DELETE query.
     *
     * @param string $sqlQuery       SQL query
     * @param string $simulatedQuery Simulated query
     *
     * @return void
     */
    public function simulatedQueryTest($sqlQuery, $simulatedQuery)
    {
        $parser = new Parser($sqlQuery);
        $analyzed_sql_results = [
            'query' => $sqlQuery,
            'parser' => $parser,
            'statement' => $parser->statements[0],
        ];

        $simulated_data = $this->import->getMatchedRows($analyzed_sql_results);

        // URL to matched rows.
        $_url_params = [
            'db'        => 'PMA',
            'sql_query' => $simulatedQuery,
        ];
        $matched_rows_url = Url::getFromRoute('/sql', $_url_params);

        $this->assertEquals(
            [
                'sql_query' => Generator::formatSql(
                    $analyzed_sql_results['query']
                ),
                'matched_rows' => 2,
                'matched_rows_url' => $matched_rows_url,
            ],
            $simulated_data
        );
    }

    /**
     * Test for checkIfRollbackPossible
     *
     * @return void
     */
    public function testPMACheckIfRollbackPossible()
    {
        $GLOBALS['db'] = 'PMA';
        //mock DBI
        $dbi = $this->getMockBuilder(DatabaseInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        // List of Transactional Engines.
        $transactional_engines = [
            'INNODB',
            'FALCON',
            'NDB',
            'INFINIDB',
            'TOKUDB',
            'XTRADB',
            'SEQUENCE',
            'BDB',
        ];

        $check_query = 'SELECT `ENGINE` FROM `information_schema`.`tables` '
            . 'WHERE `table_name` = "%s" '
            . 'AND `table_schema` = "%s" '
            . 'AND UPPER(`engine`) IN ("'
            . implode('", "', $transactional_engines)
            . '")';

        $check_table_query = 'SELECT * FROM `%s`.`%s` '
            . 'LIMIT 1';

        $dbi->expects($this->at(0))
            ->method('tryQuery')
            ->with(sprintf($check_table_query, 'PMA', 'table_1'))
            ->will($this->returnValue(['table']));

        $dbi->expects($this->at(1))
            ->method('tryQuery')
            ->with(sprintf($check_query, 'table_1', 'PMA'))
            ->will($this->returnValue(true));

        $dbi->expects($this->at(2))
            ->method('numRows')
            ->will($this->returnValue(1));

        $dbi->expects($this->at(3))
            ->method('tryQuery')
            ->with(sprintf($check_table_query, 'PMA', 'table_2'))
            ->will($this->returnValue(['table']));

        $dbi->expects($this->at(4))
            ->method('tryQuery')
            ->with(sprintf($check_query, 'table_2', 'PMA'))
            ->will($this->returnValue(true));

        $dbi->expects($this->at(5))
            ->method('numRows')
            ->will($this->returnValue(1));

        $GLOBALS['dbi'] = $dbi;

        $sqlQuery = 'UPDATE `table_1` AS t1, `table_2` t2 '
            . 'SET `table_1`.`id` = `table_2`.`id` '
            . 'WHERE 1';

        $this->assertTrue($this->import->checkIfRollbackPossible($sqlQuery));
    }

    /**
     * Data provider for testSkipByteOrderMarksFromContents
     *
     * @return array[]
     */
    public function providerContentWithByteOrderMarks(): array
    {
        return [
            [
                "\xEF\xBB\xBF blabla上海",
                ' blabla上海',
            ],
            [
                "\xEF\xBB\xBF blabla",
                ' blabla',
            ],
            [
                "\xEF\xBB\xBF blabla\xEF\xBB\xBF",
                " blabla\xEF\xBB\xBF",
            ],
            [
                "\xFE\xFF blabla",
                ' blabla',
            ],
            [
                "\xFE\xFF blabla\xFE\xFF",
                " blabla\xFE\xFF",
            ],
            [
                "\xFF\xFE blabla",
                ' blabla',
            ],
            [
                "\xFF\xFE blabla\xFF\xFE",
                " blabla\xFF\xFE",
            ],
            [
                "\xEF\xBB\xBF\x44\x52\x4F\x50\x20\x54\x41\x42\x4C\x45\x20\x49\x46\x20\x45\x58\x49\x53\x54\x53",
                'DROP TABLE IF EXISTS',
            ],
        ];
    }

    /**
     * Test for skipByteOrderMarksFromContents
     *
     * @param string $input         The contents to strip BOM
     * @param string $cleanContents The contents cleaned
     *
     * @dataProvider providerContentWithByteOrderMarks
     */
    public function testSkipByteOrderMarksFromContents(string $input, string $cleanContents): void
    {
        $this->assertEquals($cleanContents, $this->import->skipByteOrderMarksFromContents($input));
    }
}
