<?php
/**
 * Tests for PhpMyAdmin\Navigation\Nodes\NodeEventContainer class
 */

declare(strict_types=1);

namespace PhpMyAdmin\Tests\Navigation\Nodes;

use PhpMyAdmin\Navigation\NodeFactory;
use PhpMyAdmin\Tests\AbstractTestCase;
use PhpMyAdmin\Url;

/**
 * Tests for PhpMyAdmin\Navigation\Nodes\NodeEventContainer class
 */
class NodeEventContainerTest extends AbstractTestCase
{
    /**
     * SetUp for test cases
     */
    protected function setUp(): void
    {
        parent::setUp();
        parent::loadDefaultConfig();
        $GLOBALS['server'] = 0;
    }

    /**
     * Test for __construct
     *
     * @return void
     */
    public function testConstructor()
    {
        $parent = NodeFactory::getInstance('NodeEventContainer');
        $this->assertArrayHasKey(
            'text',
            $parent->links
        );
        $this->assertStringContainsString(
            Url::getFromRoute('/database/events'),
            $parent->links['text']
        );
        $this->assertEquals('events', $parent->realName);
    }
}
