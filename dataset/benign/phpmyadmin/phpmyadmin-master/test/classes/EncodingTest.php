<?php
/**
 * Tests for Charset Conversions
 */

declare(strict_types=1);

namespace PhpMyAdmin\Tests;

use PhpMyAdmin\Encoding;
use const PHP_INT_SIZE;
use function fclose;
use function file_get_contents;
use function fopen;
use function function_exists;
use function fwrite;
use function mb_convert_encoding;
use function mb_convert_kana;
use function unlink;

/**
 * Tests for Charset Conversions
 */
class EncodingTest extends AbstractTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Encoding::initEngine();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        Encoding::initEngine();
    }

    /**
     * Test for Encoding::convertString
     *
     * @return void
     *
     * @test
     * @group medium
     */
    public function testNoConversion()
    {
        $this->assertEquals(
            'test',
            Encoding::convertString('UTF-8', 'UTF-8', 'test')
        );
    }

    /**
     * @return void
     */
    public function testInvalidConversion()
    {
        // Invalid value to use default case
        Encoding::setEngine(-1);
        $this->assertEquals(
            'test',
            Encoding::convertString('UTF-8', 'anything', 'test')
        );
    }

    /**
     * @return void
     */
    public function testRecode()
    {
        if (! function_exists('recode_string')) {
            $this->markTestSkipped('recode extension missing');
        }

        Encoding::setEngine(Encoding::ENGINE_RECODE);
        $this->assertEquals(
            'Only That ecole & Can Be My Blame',
            Encoding::convertString(
                'UTF-8',
                'flat',
                'Only That école & Can Be My Blame'
            )
        );
    }

    /**
     * This group is used on debian packaging to exclude the test
     *
     * @see https://bugs.debian.org/cgi-bin/bugreport.cgi?bug=854821#27
     *
     * @return void
     *
     * @group extension-iconv
     */
    public function testIconv()
    {
        if (! function_exists('iconv')) {
            $this->markTestSkipped('iconv extension missing');
        }

        if (PHP_INT_SIZE === 8) {
            $GLOBALS['cfg']['IconvExtraParams'] = '//TRANSLIT';
            Encoding::setEngine(Encoding::ENGINE_ICONV);
            $this->assertEquals(
                "This is the Euro symbol 'EUR'.",
                Encoding::convertString(
                    'UTF-8',
                    'ISO-8859-1',
                    "This is the Euro symbol '€'."
                )
            );
        } elseif (PHP_INT_SIZE === 4) {
            // NOTE: this does not work on 32bit systems and requires "//IGNORE"
            // NOTE: or it will throw "iconv(): Detected an illegal character in input string"
            $GLOBALS['cfg']['IconvExtraParams'] = '//TRANSLIT//IGNORE';
            Encoding::setEngine(Encoding::ENGINE_ICONV);
            $this->assertEquals(
                "This is the Euro symbol ''.",
                Encoding::convertString(
                    'UTF-8',
                    'ISO-8859-1',
                    "This is the Euro symbol '€'."
                )
            );
        }
    }

    /**
     * @return void
     */
    public function testMbstring()
    {
        Encoding::setEngine(Encoding::ENGINE_MB);
        $this->assertEquals(
            "This is the Euro symbol '?'.",
            Encoding::convertString(
                'UTF-8',
                'ISO-8859-1',
                "This is the Euro symbol '€'."
            )
        );
    }

    /**
     * Test for kanjiChangeOrder
     *
     * @return void
     *
     * @test
     */
    public function testChangeOrder()
    {
        $this->assertEquals('ASCII,SJIS,EUC-JP,JIS', Encoding::getKanjiEncodings());
        Encoding::kanjiChangeOrder();
        $this->assertEquals('ASCII,EUC-JP,SJIS,JIS', Encoding::getKanjiEncodings());
        Encoding::kanjiChangeOrder();
        $this->assertEquals('ASCII,SJIS,EUC-JP,JIS', Encoding::getKanjiEncodings());
    }

    /**
     * Test for Encoding::kanjiStrConv
     *
     * @return void
     *
     * @test
     */
    public function testKanjiStrConv()
    {
        $this->assertEquals(
            'test',
            Encoding::kanjiStrConv('test', '', '')
        );

        $GLOBALS['kanji_encoding_list'] = 'ASCII,SJIS,EUC-JP,JIS';

        $this->assertEquals(
            'test è',
            Encoding::kanjiStrConv('test è', '', '')
        );

        $this->assertEquals(
            mb_convert_encoding('test è', 'ASCII', 'SJIS'),
            Encoding::kanjiStrConv('test è', 'ASCII', '')
        );

        $this->assertEquals(
            mb_convert_kana('全角', 'KV', 'SJIS'),
            Encoding::kanjiStrConv('全角', '', 'kana')
        );
    }

    /**
     * Test for Encoding::kanjiFileConv
     *
     * @return void
     *
     * @test
     */
    public function testFileConv()
    {
        $file_str = '教育漢字常用漢字';
        $filename = 'test.kanji';
        $file = fopen($filename, 'w');
        fwrite($file, $file_str);
        fclose($file);
        $GLOBALS['kanji_encoding_list'] = 'ASCII,EUC-JP,SJIS,JIS';

        $result = Encoding::kanjiFileConv($filename, 'JIS', 'kana');

        $string = file_get_contents($result);
        Encoding::kanjiChangeOrder();
        $expected = Encoding::kanjiStrConv($file_str, 'JIS', 'kana');
        Encoding::kanjiChangeOrder();
        $this->assertEquals($string, $expected);
        unlink($result);
    }

    /**
     * Test for Encoding::kanjiEncodingForm
     *
     * @return void
     *
     * @test
     */
    public function testEncodingForm()
    {
        $actual = Encoding::kanjiEncodingForm();
        $this->assertStringContainsString(
            '<input type="radio" name="knjenc"',
            $actual
        );
        $this->assertStringContainsString(
            'type="radio" name="knjenc"',
            $actual
        );
        $this->assertStringContainsString(
            '<input type="radio" name="knjenc" value="EUC-JP" id="kj-euc">',
            $actual
        );
        $this->assertStringContainsString(
            '<input type="radio" name="knjenc" value="SJIS" id="kj-sjis">',
            $actual
        );
        $this->assertStringContainsString(
            '<input type="checkbox" name="xkana" value="kana" id="kj-kana">',
            $actual
        );
    }

    /**
     * @return void
     */
    public function testListEncodings()
    {
        $GLOBALS['cfg']['AvailableCharsets'] = ['utf-8'];
        $result = Encoding::listEncodings();
        $this->assertContains('utf-8', $result);
    }
}
