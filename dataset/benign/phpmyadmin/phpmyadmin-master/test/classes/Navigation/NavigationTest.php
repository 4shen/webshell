<?php
/**
 * Test for PhpMyAdmin\Navigation\Navigation class
 */

declare(strict_types=1);

namespace PhpMyAdmin\Tests\Navigation;

use PhpMyAdmin\DatabaseInterface;
use PhpMyAdmin\Navigation\Navigation;
use PhpMyAdmin\Relation;
use PhpMyAdmin\Template;
use PhpMyAdmin\Tests\AbstractTestCase;
use PhpMyAdmin\Url;

/**
 * Tests for PhpMyAdmin\Navigation\Navigation class
 */
class NavigationTest extends AbstractTestCase
{
    /** @var Navigation */
    protected $object;

    /**
     * Sets up the fixture.
     *
     * @access protected
     */
    protected function setUp(): void
    {
        parent::setUp();
        parent::loadDefaultConfig();
        parent::setLanguage();
        $GLOBALS['server'] = 1;
        $GLOBALS['db'] = 'db';
        $GLOBALS['table'] = '';
        $GLOBALS['cfgRelation']['db'] = 'pmadb';
        $GLOBALS['cfgRelation']['navigationhiding'] = 'navigationhiding';
        $GLOBALS['cfg']['Server']['user'] = 'user';
        $GLOBALS['cfg']['Server']['DisableIS'] = false;
        $GLOBALS['cfg']['ActionLinksMode'] = 'both';
        $GLOBALS['pmaThemeImage'] = '';

        $this->object = new Navigation(
            new Template(),
            new Relation($GLOBALS['dbi']),
            $GLOBALS['dbi']
        );
        $GLOBALS['cfgRelation']['db'] = 'pmadb';
        $GLOBALS['cfgRelation']['navigationhiding'] = 'navigationhiding';
    }

    /**
     * Tears down the fixture.
     *
     * @access protected
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        unset($this->object);
    }

    /**
     * Tests hideNavigationItem() method.
     *
     * @return void
     *
     * @test
     */
    public function testHideNavigationItem()
    {
        $expectedQuery = 'INSERT INTO `pmadb`.`navigationhiding`'
            . '(`username`, `item_name`, `item_type`, `db_name`, `table_name`)'
            . " VALUES ('user','itemName','itemType','db','')";
        $dbi = $this->getMockBuilder(DatabaseInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dbi->expects($this->once())
            ->method('tryQuery')
            ->with($expectedQuery);
        $dbi->expects($this->any())->method('escapeString')
            ->will($this->returnArgument(0));

        $GLOBALS['dbi'] = $dbi;
        $this->object = new Navigation(new Template(), new Relation($dbi), $dbi);
        $this->object->hideNavigationItem('itemName', 'itemType', 'db');
    }

    /**
     * Tests unhideNavigationItem() method.
     *
     * @return void
     *
     * @test
     */
    public function testUnhideNavigationItem()
    {
        $expectedQuery = 'DELETE FROM `pmadb`.`navigationhiding`'
            . " WHERE `username`='user' AND `item_name`='itemName'"
            . " AND `item_type`='itemType' AND `db_name`='db'";
        $dbi = $this->getMockBuilder(DatabaseInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dbi->expects($this->once())
            ->method('tryQuery')
            ->with($expectedQuery);

        $dbi->expects($this->any())->method('escapeString')
            ->will($this->returnArgument(0));
        $GLOBALS['dbi'] = $dbi;
        $this->object = new Navigation(new Template(), new Relation($dbi), $dbi);
        $this->object->unhideNavigationItem('itemName', 'itemType', 'db');
    }

    /**
     * Tests getItemUnhideDialog() method.
     *
     * @return void
     *
     * @test
     */
    public function testGetItemUnhideDialog()
    {
        $html = $this->object->getItemUnhideDialog('db');
        $this->assertStringContainsString(
            '<td>tableName</td>',
            $html
        );
        $this->assertStringContainsString(
            '<a class="unhideNavItem ajax" href="' . Url::getFromRoute('/navigation') . '" data-post="'
            . 'unhideNavItem=1&amp;itemType=table&amp;'
            . 'itemName=tableName&amp;dbName=db&amp;lang=en">',
            $html
        );
    }
}
