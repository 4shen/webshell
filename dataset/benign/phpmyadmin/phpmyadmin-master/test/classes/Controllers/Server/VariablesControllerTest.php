<?php
/**
 * Holds VariablesControllerTest class
 */

declare(strict_types=1);

namespace PhpMyAdmin\Tests\Controllers\Server;

use PhpMyAdmin\Controllers\Server\VariablesController;
use PhpMyAdmin\DatabaseInterface;
use PhpMyAdmin\Html\Generator;
use PhpMyAdmin\Response;
use PhpMyAdmin\Template;
use PhpMyAdmin\Tests\AbstractTestCase;
use PhpMyAdmin\Tests\Stubs\Response as ResponseStub;
use Williamdes\MariaDBMySQLKBS\Search as KBSearch;
use Williamdes\MariaDBMySQLKBS\SlimData as KBSlimData;
use function htmlspecialchars;
use function str_replace;

/**
 * Tests for VariablesController class
 */
class VariablesControllerTest extends AbstractTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        parent::defineVersionConstants();
        parent::setGlobalConfig();
        parent::setLanguage();
        $GLOBALS['PMA_Config']->enableBc();

        $GLOBALS['text_dir'] = 'ltr';
        $GLOBALS['server'] = 1;
        $GLOBALS['db'] = 'db';
        $GLOBALS['table'] = 'table';
        $GLOBALS['PMA_PHP_SELF'] = 'index.php';
        $GLOBALS['cfg']['Server']['DisableIS'] = false;

        $dbi = $this->getMockBuilder(DatabaseInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        //this data is needed when PhpMyAdmin\Server\Status\Data constructs
        $serverSessionVariables = [
            'auto_increment_increment' => '1',
            'auto_increment_offset' => '13',
            'automatic_sp_privileges' => 'ON',
            'back_log' => '50',
            'big_tables' => 'OFF',
        ];

        $serverGlobalVariables = [
            'auto_increment_increment' => '0',
            'auto_increment_offset' => '12',
        ];

        $fetchResult = [
            [
                'SHOW SESSION VARIABLES;',
                0,
                1,
                DatabaseInterface::CONNECT_USER,
                0,
                $serverSessionVariables,
            ],
            [
                'SHOW GLOBAL VARIABLES;',
                0,
                1,
                DatabaseInterface::CONNECT_USER,
                0,
                $serverGlobalVariables,
            ],
        ];

        $dbi->expects($this->any())->method('fetchResult')
            ->will($this->returnValueMap($fetchResult));

        $GLOBALS['dbi'] = $dbi;
    }

    public function testIndex(): void
    {
        $response = new ResponseStub();

        $controller = new VariablesController(
            $response,
            $GLOBALS['dbi'],
            new Template()
        );

        $controller->index();
        $html = $response->getHTMLResult();

        $this->assertStringContainsString(
            Generator::getIcon('b_save', __('Save')),
            $html
        );
        $this->assertStringContainsString(
            Generator::getIcon('b_close', __('Cancel')),
            $html
        );
        $this->assertStringContainsString(
            '<legend>' . __('Filters') . '</legend>',
            $html
        );
        $this->assertStringContainsString(
            __('Containing the word:'),
            $html
        );
        $this->assertStringContainsString(
            __('Variable'),
            $html
        );
        $this->assertStringContainsString(
            __('Value'),
            $html
        );

        $name = 'auto_increment_increment';
        $value = htmlspecialchars(str_replace('_', ' ', $name));
        $this->assertStringContainsString(
            $value,
            $html
        );
        $name = 'auto_increment_offset';
        $value = htmlspecialchars(str_replace('_', ' ', $name));
        $this->assertStringContainsString(
            $value,
            $html
        );
    }

    /**
     * Test for formatVariable()
     */
    public function testFormatVariable(): void
    {
        $controller = new VariablesController(
            Response::getInstance(),
            $GLOBALS['dbi'],
            new Template()
        );

        $nameForValueByte = 'byte_variable';
        $nameForValueNotByte = 'not_a_byte_variable';

        $slimData = new KBSlimData();
        $slimData->addVariable($nameForValueByte, 'byte', null);
        $slimData->addVariable($nameForValueNotByte, 'string', null);
        KBSearch::loadTestData($slimData);

        //name is_numeric and the value type is byte
        $args = [
            $nameForValueByte,
            '3',
        ];
        [$formattedValue, $isHtmlFormatted] = $this->callFunction(
            $controller,
            VariablesController::class,
            'formatVariable',
            $args
        );
        $this->assertEquals(
            '<abbr title="3">3 B</abbr>',
            $formattedValue
        );
        $this->assertTrue($isHtmlFormatted);

        //name is_numeric and the value type is not byte
        $args = [
            $nameForValueNotByte,
            '3',
        ];
        [$formattedValue, $isHtmlFormatted] = $this->callFunction(
            $controller,
            VariablesController::class,
            'formatVariable',
            $args
        );
        $this->assertEquals(
            '3',
            $formattedValue
        );
        $this->assertFalse($isHtmlFormatted);

        //value is not a number
        $args = [
            $nameForValueNotByte,
            'value',
        ];
        [$formattedValue, $isHtmlFormatted] = $this->callFunction(
            $controller,
            VariablesController::class,
            'formatVariable',
            $args
        );
        $this->assertEquals(
            'value',
            $formattedValue
        );
        $this->assertFalse($isHtmlFormatted);
    }
}
