<?php
/**
 * Tests for PhpMyAdmin\Navigation\Nodes\NodeTable class
 */

declare(strict_types=1);

namespace PhpMyAdmin\Tests\Navigation\Nodes;

use PhpMyAdmin\Navigation\NodeFactory;
use PhpMyAdmin\Tests\AbstractTestCase;

/**
 * Tests for PhpMyAdmin\Navigation\Nodes\NodeTable class
 */
class NodeTableTest extends AbstractTestCase
{
    /**
     * SetUp for test cases
     */
    protected function setUp(): void
    {
        parent::setUp();
        parent::loadDefaultConfig();

        $GLOBALS['server'] = 0;
        $GLOBALS['cfg']['NavigationTreeDefaultTabTable'] = 'b_browse';
        $GLOBALS['cfg']['NavigationTreeDefaultTabTable2'] = '';
        $GLOBALS['cfg']['DefaultTabTable'] = 'browse';
        $GLOBALS['cfg']['MaxNavigationItems'] = 250;
        $GLOBALS['cfg']['NavigationTreeEnableGrouping'] = true;
        $GLOBALS['cfg']['NavigationTreeDbSeparator'] = '_';
        $GLOBALS['cfg']['NavigationTreeTableSeparator'] = '__';
        $GLOBALS['cfg']['NavigationTreeTableLevel'] = 1;
    }

    /**
     * Test for __construct
     *
     * @return void
     */
    public function testConstructor()
    {
        $parent = NodeFactory::getInstance('NodeTable');
        $this->assertArrayHasKey(
            'text',
            $parent->links
        );
        $this->assertStringContainsString(
            'index.php?route=/sql',
            $parent->links['text']
        );
        $this->assertStringContainsString('table', $parent->classes);
    }

    /**
     * Tests whether the node icon is properly set based on the icon target.
     *
     * @param string $target    target of the icon
     * @param string $imageName name of the image that should be set
     *
     * @dataProvider providerForTestIcon
     */
    public function testIcon($target, $imageName): void
    {
        $GLOBALS['cfg']['NavigationTreeDefaultTabTable'] = $target;
        $node = NodeFactory::getInstance('NodeTable');
        $this->assertStringContainsString($imageName, $node->icon[0]);
    }

    /**
     * Data provider for testIcon().
     *
     * @return array data for testIcon()
     */
    public function providerForTestIcon()
    {
        return [
            [
                'structure',
                'b_props',
            ],
            [
                'search',
                'b_search',
            ],
            [
                'insert',
                'b_insrow',
            ],
            [
                'sql',
                'b_sql',
            ],
            [
                'browse',
                'b_browse',
            ],
        ];
    }
}
