<?php
/**
 * Tests for PhpMyAdmin\BrowseForeigners
 */

declare(strict_types=1);

namespace PhpMyAdmin\Tests;

use PhpMyAdmin\BrowseForeigners;
use PhpMyAdmin\Template;

/**
 * Tests for PhpMyAdmin\BrowseForeigners
 */
class BrowseForeignersTest extends AbstractTestCase
{
    private $browseForeigners;

    /**
     * Setup for test cases
     */
    protected function setUp(): void
    {
        parent::setUp();
        $GLOBALS['cfg']['LimitChars'] = 50;
        $GLOBALS['cfg']['MaxRows'] = 25;
        $GLOBALS['cfg']['RepeatCells'] = 100;
        $GLOBALS['cfg']['ShowAll'] = false;
        $GLOBALS['pmaThemeImage'] = '';
        $this->browseForeigners = new BrowseForeigners(new Template());
    }

    /**
     * Test for BrowseForeigners::getForeignLimit
     *
     * @return void
     */
    public function testGetForeignLimit()
    {
        $this->assertNull(
            $this->browseForeigners->getForeignLimit('Show all')
        );

        $this->assertEquals(
            'LIMIT 0, 25 ',
            $this->browseForeigners->getForeignLimit(null)
        );

        $_POST['pos'] = 10;

        $this->assertEquals(
            'LIMIT 10, 25 ',
            $this->browseForeigners->getForeignLimit(null)
        );

        $GLOBALS['cfg']['MaxRows'] = 50;
        $browseForeigners = new BrowseForeigners(new Template());

        $this->assertEquals(
            'LIMIT 10, 50 ',
            $browseForeigners->getForeignLimit(null)
        );

        $this->assertEquals(
            'LIMIT 10, 50 ',
            $browseForeigners->getForeignLimit('xyz')
        );
    }

    /**
     * Test for BrowseForeigners::getHtmlForGotoPage
     *
     * @return void
     */
    public function testGetHtmlForGotoPage()
    {
        $this->assertEquals(
            '',
            $this->callFunction(
                $this->browseForeigners,
                BrowseForeigners::class,
                'getHtmlForGotoPage',
                [null]
            )
        );

        $_POST['pos'] = 15;
        $foreignData = [];
        $foreignData['disp_row'] = [];
        $foreignData['the_total'] = 5;

        $this->assertEquals(
            '',
            $this->callFunction(
                $this->browseForeigners,
                BrowseForeigners::class,
                'getHtmlForGotoPage',
                [$foreignData]
            )
        );

        $foreignData['the_total'] = 30;
        $result = $this->callFunction(
            $this->browseForeigners,
            BrowseForeigners::class,
            'getHtmlForGotoPage',
            [$foreignData]
        );

        $this->assertStringStartsWith(
            'Page number:',
            $result
        );

        $this->assertStringEndsWith(
            '</select>',
            $result
        );

        $this->assertStringContainsString(
            '<select class="pageselector ajax" name="pos"',
            $result
        );

        $this->assertStringContainsString(
            '<option selected="selected" '
            . 'style="font-weight: bold" value="0">',
            $result
        );

        $this->assertStringContainsString(
            '<option  value="25"',
            $result
        );
    }

    /**
     * Test for BrowseForeigners::getDescriptionAndTitle
     *
     * @return void
     */
    public function testGetDescriptionAndTitle()
    {
        $desc = 'foobar<baz';

        $this->assertEquals(
            [
                'foobar&lt;baz',
                '',
            ],
            $this->callFunction(
                $this->browseForeigners,
                BrowseForeigners::class,
                'getDescriptionAndTitle',
                [$desc]
            )
        );

        $GLOBALS['cfg']['LimitChars'] = 5;
        $browseForeigners = new BrowseForeigners(new Template());

        $this->assertEquals(
            [
                'fooba...',
                'foobar&lt;baz',
            ],
            $this->callFunction(
                $browseForeigners,
                BrowseForeigners::class,
                'getDescriptionAndTitle',
                [$desc]
            )
        );
    }

    /**
     * Test for BrowseForeigners::getHtmlForRelationalFieldSelection
     *
     * @return void
     */
    public function testGetHtmlForRelationalFieldSelection()
    {
        $db = '';
        $table = '';
        $field = 'foo';
        $foreignData = [];
        $foreignData['disp_row'] = '';
        $fieldkey = 'bar';
        $current_value = '';
        $_POST['rownumber'] = 1;
        $_POST['foreign_filter'] = '5';
        $result = $this->browseForeigners->getHtmlForRelationalFieldSelection(
            $db,
            $table,
            $field,
            $foreignData,
            $fieldkey,
            $current_value
        );

        $this->assertStringContainsString(
            '<form class="ajax" '
            . 'id="browse_foreign_form" name="browse_foreign_from" '
            . 'action="index.php?route=/browse-foreigners',
            $result
        );
        $this->assertStringContainsString(
            '" method="post">',
            $result
        );

        $this->assertStringContainsString(
            '<fieldset>',
            $result
        );

        $this->assertStringContainsString(
            '<input type="hidden" name="field" value="foo">',
            $result
        );

        $this->assertStringContainsString(
            '<input type="hidden" name="fieldkey" value="bar">',
            $result
        );

        $this->assertStringContainsString(
            '<input type="hidden" name="rownumber" value="1">',
            $result
        );

        $this->assertStringContainsString(
            '<span class="formelement">',
            $result
        );

        $this->assertStringContainsString(
            '<label for="input_foreign_filter">',
            $result
        );

        $this->assertStringContainsString(
            '<input type="text" name="foreign_filter" '
            . 'id="input_foreign_filter" '
            . 'value="5" data-old="5" '
            . '>',
            $result
        );

        $this->assertStringContainsString(
            '<input class="btn btn-primary" type="submit" name="submit_foreign_filter" value="Go">',
            $result
        );

        $this->assertStringContainsString(
            '<span class="formelement">',
            $result
        );

        $this->assertStringContainsString(
            '<table width="100%" id="browse_foreign_table">',
            $result
        );

        $foreignData['disp_row'] = [];
        $foreignData['the_total'] = 5;
        $result = $this->browseForeigners->getHtmlForRelationalFieldSelection(
            $db,
            $table,
            $field,
            $foreignData,
            $fieldkey,
            $current_value
        );

        $this->assertStringContainsString(
            '<table width="100%" id="browse_foreign_table">',
            $result
        );

        $this->assertStringContainsString(
            '<th>',
            $result
        );
    }
}
