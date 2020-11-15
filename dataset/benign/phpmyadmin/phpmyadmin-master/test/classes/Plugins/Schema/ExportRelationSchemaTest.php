<?php
/**
 * Tests for PhpMyAdmin\Plugins\Schema\ExportRelationSchema class
 */

declare(strict_types=1);

namespace PhpMyAdmin\Tests\Plugins\Schema;

use PhpMyAdmin\Plugins\Schema\ExportRelationSchema;
use PhpMyAdmin\Tests\AbstractTestCase;

/**
 * Tests for PhpMyAdmin\Plugins\Schema\ExportRelationSchema class
 */
class ExportRelationSchemaTest extends AbstractTestCase
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
        parent::defineVersionConstants();
        $_REQUEST['page_number'] = 33;
        $this->object = new ExportRelationSchema('information_schema', null);
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
     * Test for setPageNumber
     *
     * @return void
     *
     * @group medium
     */
    public function testSetPageNumber()
    {
        $this->object->setPageNumber(33);
        $this->assertEquals(
            33,
            $this->object->getPageNumber()
        );
    }

    /**
     * Test for setShowColor
     *
     * @return void
     *
     * @group medium
     */
    public function testSetShowColor()
    {
        $this->object->setShowColor(true);
        $this->assertTrue(
            $this->object->isShowColor()
        );
        $this->object->setShowColor(false);
        $this->assertFalse(
            $this->object->isShowColor()
        );
    }

    /**
     * Test for setOrientation
     *
     * @return void
     *
     * @group medium
     */
    public function testSetOrientation()
    {
        $this->object->setOrientation('P');
        $this->assertEquals(
            'P',
            $this->object->getOrientation()
        );
        $this->object->setOrientation('A');
        $this->assertEquals(
            'L',
            $this->object->getOrientation()
        );
    }

    /**
     * Test for setTableDimension
     *
     * @return void
     *
     * @group medium
     */
    public function testSetTableDimension()
    {
        $this->object->setTableDimension(true);
        $this->assertTrue(
            $this->object->isTableDimension()
        );
        $this->object->setTableDimension(false);
        $this->assertFalse(
            $this->object->isTableDimension()
        );
    }

    /**
     * Test for setPaper
     *
     * @return void
     *
     * @group medium
     */
    public function testSetPaper()
    {
        $this->object->setPaper('A5');
        $this->assertEquals(
            'A5',
            $this->object->getPaper()
        );
        $this->object->setPaper('A4');
        $this->assertEquals(
            'A4',
            $this->object->getPaper()
        );
    }

    /**
     * Test for setAllTablesSameWidth
     *
     * @return void
     *
     * @group medium
     */
    public function testSetAllTablesSameWidth()
    {
        $this->object->setAllTablesSameWidth(true);
        $this->assertTrue(
            $this->object->isAllTableSameWidth()
        );
        $this->object->setAllTablesSameWidth(false);
        $this->assertFalse(
            $this->object->isAllTableSameWidth()
        );
    }

    /**
     * Test for setShowKeys
     *
     * @return void
     *
     * @group medium
     */
    public function testSetShowKeys()
    {
        $this->object->setShowKeys(true);
        $this->assertTrue(
            $this->object->isShowKeys()
        );
        $this->object->setShowKeys(false);
        $this->assertFalse(
            $this->object->isShowKeys()
        );
    }
}
