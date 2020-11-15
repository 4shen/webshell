<?php
/**
 * Holds VariablesControllerTest
 */

declare(strict_types=1);

namespace PhpMyAdmin\Tests\Controllers\Server\Status;

use PhpMyAdmin\Controllers\Server\Status\VariablesController;
use PhpMyAdmin\DatabaseInterface;
use PhpMyAdmin\Server\Status\Data;
use PhpMyAdmin\Template;
use PhpMyAdmin\Tests\AbstractTestCase;
use PhpMyAdmin\Tests\Stubs\Response;

class VariablesControllerTest extends AbstractTestCase
{
    /** @var Data */
    private $data;

    protected function setUp(): void
    {
        parent::setUp();
        parent::defineVersionConstants();
        parent::setGlobalConfig();
        $GLOBALS['PMA_Config']->enableBc();

        $GLOBALS['text_dir'] = 'ltr';
        $GLOBALS['server'] = 1;
        $GLOBALS['db'] = 'db';
        $GLOBALS['table'] = 'table';
        $GLOBALS['PMA_PHP_SELF'] = 'index.php';
        $GLOBALS['cfg']['Server']['DisableIS'] = false;
        $GLOBALS['cfg']['Server']['host'] = 'localhost';
        $GLOBALS['replication_info']['master']['status'] = true;
        $GLOBALS['replication_info']['slave']['status'] = true;
        $GLOBALS['replication_types'] = [];

        $serverStatus = [
            'Aborted_clients' => '0',
            'Aborted_connects' => '0',
            'Com_delete_multi' => '0',
            'Com_create_function' => '0',
            'Com_empty_query' => '0',
        ];

        $serverVariables = [
            'auto_increment_increment' => '1',
            'auto_increment_offset' => '1',
            'automatic_sp_privileges' => 'ON',
            'back_log' => '50',
            'big_tables' => 'OFF',
        ];

        $fetchResult = [
            [
                'SHOW GLOBAL STATUS',
                0,
                1,
                DatabaseInterface::CONNECT_USER,
                0,
                $serverStatus,
            ],
            [
                'SHOW GLOBAL VARIABLES',
                0,
                1,
                DatabaseInterface::CONNECT_USER,
                0,
                $serverVariables,
            ],
            [
                "SELECT concat('Com_', variable_name), variable_value "
                . 'FROM data_dictionary.GLOBAL_STATEMENTS',
                0,
                1,
                DatabaseInterface::CONNECT_USER,
                0,
                $serverStatus,
            ],
        ];

        $dbi = $this->getMockBuilder(DatabaseInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dbi->expects($this->at(0))
            ->method('tryQuery')
            ->with('SHOW GLOBAL STATUS')
            ->will($this->returnValue(true));

        $dbi->expects($this->at(1))
            ->method('fetchRow')
            ->will($this->returnValue(['Aborted_clients', '0']));
        $dbi->expects($this->at(2))
            ->method('fetchRow')
            ->will($this->returnValue(['Aborted_connects', '0']));
        $dbi->expects($this->at(3))
            ->method('fetchRow')
            ->will($this->returnValue(['Com_delete_multi', '0']));
        $dbi->expects($this->at(4))
            ->method('fetchRow')
            ->will($this->returnValue(['Com_create_function', '0']));
        $dbi->expects($this->at(5))
            ->method('fetchRow')
            ->will($this->returnValue(['Com_empty_query', '0']));
        $dbi->expects($this->at(6))
            ->method('fetchRow')
            ->will($this->returnValue(false));

        $dbi->expects($this->at(7))->method('freeResult');

        $dbi->expects($this->any())->method('fetchResult')
            ->will($this->returnValueMap($fetchResult));

        $GLOBALS['dbi'] = $dbi;

        $this->data = new Data();
    }

    public function testIndex(): void
    {
        $response = new Response();

        $controller = new VariablesController(
            $response,
            $GLOBALS['dbi'],
            new Template(),
            $this->data
        );

        $controller->index();
        $html = $response->getHTMLResult();

        $this->assertStringContainsString(
            '<fieldset id="tableFilter">',
            $html
        );
        $this->assertStringContainsString(
            'index.php?route=/server/status/variables',
            $html
        );

        $this->assertStringContainsString(
            '<label for="filterText">Containing the word:</label>',
            $html
        );

        $this->assertStringContainsString(
            '<label for="filterAlert">',
            $html
        );
        $this->assertStringContainsString(
            'Show only alert values',
            $html
        );
        $this->assertStringContainsString(
            'Filter by category',
            $html
        );
        $this->assertStringContainsString(
            'Show unformatted values',
            $html
        );

        $this->assertStringContainsString(
            '<div id="linkSuggestions" class="defaultLinks hide"',
            $html
        );

        $this->assertStringContainsString(
            'Related links:',
            $html
        );
        $this->assertStringContainsString(
            'Flush (close) all tables',
            $html
        );
        $this->assertStringContainsString(
            '<span class="status_binlog_cache">',
            $html
        );

        $this->assertStringContainsString(
            '<table class="data noclick" id="serverstatusvariables">',
            $html
        );
        $this->assertStringContainsString(
            '<th>Variable</th>',
            $html
        );
        $this->assertStringContainsString(
            '<th>Value</th>',
            $html
        );
        $this->assertStringContainsString(
            '<th>Description</th>',
            $html
        );

        $this->assertStringContainsString(
            'Aborted clients',
            $html
        );
        $this->assertStringContainsString(
            '<span class="allfine">',
            $html
        );
        $this->assertStringContainsString(
            'Aborted connects',
            $html
        );
        $this->assertStringContainsString(
            'Com delete multi',
            $html
        );
        $this->assertStringContainsString(
            'Com create function',
            $html
        );
        $this->assertStringContainsString(
            'Com empty query',
            $html
        );
    }
}
