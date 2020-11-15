<?php
/**
 * Tests for PMA_StorageEngine_memory
 */

declare(strict_types=1);

namespace PhpMyAdmin\Tests\Engines;

use PhpMyAdmin\Engines\Memory;
use PhpMyAdmin\Tests\AbstractTestCase;

/**
 * Tests for PhpMyAdmin\Engines\Memory
 */
class MemoryTest extends AbstractTestCase
{
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
        $GLOBALS['server'] = 0;
        $this->object = new Memory('memory');
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
     * Test for getVariables
     *
     * @return void
     */
    public function testGetVariables()
    {
        $this->assertEquals(
            $this->object->getVariables(),
            [
                'max_heap_table_size' => ['type' => 1],
            ]
        );
    }
}
