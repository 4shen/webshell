<?php
/**
 * Tests for Footer class
 */

declare(strict_types=1);

namespace PhpMyAdmin\Tests;

use PhpMyAdmin\ErrorHandler;
use PhpMyAdmin\Footer;
use function json_encode;

/**
 * Tests for Footer class
 */
class FooterTest extends AbstractTestCase
{
    /** @var array store private attributes of PhpMyAdmin\Footer */
    public $privates = [];

    /** @access protected */
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
        parent::setLanguage();
        $_SERVER['SCRIPT_NAME'] = 'index.php';
        $GLOBALS['PMA_PHP_SELF'] = 'index.php';
        $GLOBALS['db'] = '';
        $GLOBALS['table'] = '';
        $GLOBALS['text_dir'] = 'ltr';
        parent::setGlobalConfig();
        $GLOBALS['PMA_Config']->enableBc();
        $GLOBALS['cfg']['Server']['DisableIS'] = false;
        $GLOBALS['cfg']['Server']['verbose'] = 'verbose host';
        $GLOBALS['server'] = '1';
        $_GET['reload_left_frame'] = '1';
        $GLOBALS['focus_querywindow'] = 'main_pane_left';
        $this->object = new Footer();
        unset($GLOBALS['error_message']);
        unset($GLOBALS['sql_query']);
        $GLOBALS['error_handler'] = new ErrorHandler();
        unset($_POST);
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
     * Test for getDebugMessage
     *
     * @return void
     *
     * @group medium
     */
    public function testGetDebugMessage()
    {
        $GLOBALS['cfg']['DBG']['sql'] = true;
        $_SESSION['debug']['queries'] = [
            [
                'count' => 1,
                'time' => 0.2,
                'query' => 'SELECT * FROM `pma_bookmark` WHERE 1',
            ],
            [
                'count' => 1,
                'time' => 2.5,
                'query' => 'SELECT * FROM `db` WHERE 1',
            ],
        ];

        $this->assertEquals(
            '{"queries":[{"count":1,"time":0.2,"query":"SELECT * FROM `pma_bookmark` WHERE 1"},'
            . '{"count":1,"time":2.5,"query":"SELECT * FROM `db` WHERE 1"}]}',
            $this->object->getDebugMessage()
        );
    }

    /**
     * Test for removeRecursion
     *
     * @return void
     */
    public function testRemoveRecursion()
    {
        $object = (object) [];
        $object->child = (object) [];
        $object->child->parent = $object;

        $this->callFunction(
            $this->object,
            Footer::class,
            'removeRecursion',
            [
                &$object,
            ]
        );

        $this->assertEquals(
            '{"child":{"parent":"***RECURSION***"}}',
            json_encode($object)
        );
    }

    /**
     * Test for getSelfLink
     *
     * @return void
     */
    public function testGetSelfLink()
    {
        $GLOBALS['cfg']['TabsMode'] = 'text';
        $GLOBALS['cfg']['ServerDefault'] = 1;
        $GLOBALS['db'] = 'db';
        $GLOBALS['table'] = 'table';

        $this->assertEquals(
            '<div id="selflink" class="print_ignore"><a href="index.php?db=db&amp;'
            . 'table=table&amp;server=1&amp;lang=en'
            . '" title="Open new phpMyAdmin window" '
            . 'target="_blank" rel="noopener noreferrer">Open new phpMyAdmin window</a></div>',
            $this->callFunction(
                $this->object,
                Footer::class,
                'getSelfLink',
                [
                    $this->object->getSelfUrl(),
                ]
            )
        );
    }

    /**
     * Test for getSelfLink
     *
     * @return void
     */
    public function testGetSelfLinkWithImage()
    {
        $GLOBALS['cfg']['TabsMode'] = 'icons';
        $GLOBALS['cfg']['ServerDefault'] = 1;

        $this->assertEquals(
            '<div id="selflink" class="print_ignore"><a href="'
            . 'index.php?server=1&amp;lang=en" title="Open new phpMyAdmin window" '
            . 'target="_blank" rel="noopener noreferrer"><img src="themes/dot.gif" title="Open new '
            . 'phpMyAdmin window" alt="Open new phpMyAdmin window" '
            . 'class="icon ic_window-new"></a></div>',
            $this->callFunction(
                $this->object,
                Footer::class,
                'getSelfLink',
                [
                    $this->object->getSelfUrl(),
                ]
            )
        );
    }

    /**
     * Test for getSelfLink
     *
     * @return void
     */
    public function testGetSelfLinkWithRoute()
    {
        $GLOBALS['route'] = '/test';
        $GLOBALS['cfg']['TabsMode'] = 'text';
        $GLOBALS['cfg']['ServerDefault'] = 1;

        $this->assertEquals(
            '<div id="selflink" class="print_ignore"><a href="index.php?route=%2Ftest'
            . '&amp;server=1&amp;lang=en'
            . '" title="Open new phpMyAdmin window" '
            . 'target="_blank" rel="noopener noreferrer">Open new phpMyAdmin window</a></div>',
            $this->callFunction(
                $this->object,
                Footer::class,
                'getSelfLink',
                [
                    $this->object->getSelfUrl(),
                ]
            )
        );
    }

    /**
     * Test for disable
     *
     * @return void
     */
    public function testDisable()
    {
        $footer = new Footer();
        $footer->disable();
        $this->assertEquals(
            '',
            $footer->getDisplay()
        );
    }

    /**
     * Test for footer when ajax enabled
     *
     * @return void
     */
    public function testAjax()
    {
        $footer = new Footer();
        $footer->setAjax(true);
        $this->assertEquals(
            '',
            $footer->getDisplay()
        );
    }

    /**
     * Test for footer get Scripts
     *
     * @return void
     */
    public function testGetScripts()
    {
        $footer = new Footer();
        $this->assertStringContainsString(
            '<script data-cfasync="false" type="text/javascript">',
            $footer->getScripts()->getDisplay()
        );
    }

    /**
     * Test for displaying footer
     *
     * @return void
     *
     * @group medium
     */
    public function testDisplay()
    {
        $footer = new Footer();
        $this->assertStringContainsString(
            'Open new phpMyAdmin window',
            $footer->getDisplay()
        );
    }

    /**
     * Test for minimal footer
     *
     * @return void
     */
    public function testMinimal()
    {
        $footer = new Footer();
        $footer->setMinimal();
        $this->assertEquals(
            "  </div>\n  </body>\n</html>\n",
            $footer->getDisplay()
        );
    }
}
