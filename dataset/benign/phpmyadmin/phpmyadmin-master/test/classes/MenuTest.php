<?php
/**
 * Test for Menu class
 */

declare(strict_types=1);

namespace PhpMyAdmin\Tests;

use PhpMyAdmin\Core;
use PhpMyAdmin\Menu;
use function define;
use function defined;

/**
 * Test for Menu class
 */
class MenuTest extends AbstractTestCase
{
    /**
     * Configures global environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        parent::defineVersionConstants();
        parent::setTheme();
        parent::loadDefaultConfig();

        if (! defined('PMA_IS_WINDOWS')) {
            define('PMA_IS_WINDOWS', false);
        }
        $GLOBALS['cfg']['Server']['DisableIS'] = false;
        $GLOBALS['server'] = 0;
        $GLOBALS['cfg']['Server']['verbose'] = 'verbose host';
        $GLOBALS['pmaThemePath'] = $GLOBALS['PMA_Theme']->getPath();
        $GLOBALS['PMA_PHP_SELF'] = Core::getenv('PHP_SELF');
        $GLOBALS['server'] = 'server';
        $GLOBALS['db'] = 'pma_test';
        $GLOBALS['table'] = 'table1';
    }

    /**
     * Server menu test
     *
     * @return void
     */
    public function testServer()
    {
        $menu = new Menu('', '');
        $this->assertStringContainsString(
            'floating_menubar',
            $menu->getDisplay()
        );
    }

    /**
     * Database menu test
     *
     * @return void
     */
    public function testDatabase()
    {
        $menu = new Menu('pma_test', '');
        $this->assertStringContainsString(
            'floating_menubar',
            $menu->getDisplay()
        );
    }

    /**
     * Table menu test
     *
     * @return void
     */
    public function testTable()
    {
        $menu = new Menu('pma_test', 'table1');
        $this->assertStringContainsString(
            'floating_menubar',
            $menu->getDisplay()
        );
    }

    /**
     * Table menu display test
     *
     * @return void
     */
    public function testTableDisplay()
    {
        $menu = new Menu('pma_test', '');
        $this->expectOutputString(
            $menu->getDisplay()
        );
        $menu->display();
    }

    /**
     * Table menu setTable test
     *
     * @return void
     */
    public function testSetTable()
    {
        $menu = new Menu('pma_test', '');
        $menu->setTable('table1');
        $this->assertStringContainsString(
            'table1',
            $menu->getDisplay()
        );
    }
}
