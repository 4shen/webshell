<?php
/**
 * Tests for Bookmark class
 */

declare(strict_types=1);

namespace PhpMyAdmin\Tests;

use PhpMyAdmin\Bookmark;

/**
 * Tests for Bookmark class
 */
class BookmarkTest extends AbstractTestCase
{
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
        $GLOBALS['cfg']['Server']['user'] = 'root';
        $GLOBALS['cfg']['Server']['pmadb'] = 'phpmyadmin';
        $GLOBALS['cfg']['Server']['bookmarktable'] = 'pma_bookmark';
        $GLOBALS['server'] = 1;
    }

    /**
     * Tests for Bookmark:getParams()
     *
     * @return void
     */
    public function testGetParams()
    {
        $this->assertFalse(
            Bookmark::getParams($GLOBALS['cfg']['Server']['user'])
        );
    }

    /**
     * Tests for Bookmark::getList()
     *
     * @return void
     */
    public function testGetList()
    {
        $this->assertEquals(
            [],
            Bookmark::getList(
                $GLOBALS['dbi'],
                $GLOBALS['cfg']['Server']['user'],
                'phpmyadmin'
            )
        );
    }

    /**
     * Tests for Bookmark::get()
     *
     * @return void
     */
    public function testGet()
    {
        $this->assertNull(
            Bookmark::get(
                $GLOBALS['dbi'],
                $GLOBALS['cfg']['Server']['user'],
                'phpmyadmin',
                '1'
            )
        );
    }

    /**
     * Tests for Bookmark::save()
     *
     * @return void
     */
    public function testSave()
    {
        $bookmarkData = [
            'bkm_database' => 'phpmyadmin',
            'bkm_user' => 'root',
            'bkm_sql_query' => 'SELECT "phpmyadmin"',
            'bkm_label' => 'bookmark1',
        ];

        $bookmark = Bookmark::createBookmark(
            $GLOBALS['dbi'],
            $GLOBALS['cfg']['Server']['user'],
            $bookmarkData
        );
        $this->assertFalse($bookmark->save());
    }
}
