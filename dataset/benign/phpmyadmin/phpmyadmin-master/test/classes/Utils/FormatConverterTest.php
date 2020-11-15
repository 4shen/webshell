<?php
/**
 * tests for PhpMyAdmin\Utils\FormatConverter class
 */

declare(strict_types=1);

namespace PhpMyAdmin\Tests\Utils;

use PhpMyAdmin\Tests\AbstractTestCase;
use PhpMyAdmin\Utils\FormatConverter;

class FormatConverterTest extends AbstractTestCase
{
    /**
     * Test for binaryToIp
     *
     * @param string $expected Expected result given an input
     * @param string $input    Input to convert
     *
     * @return void
     *
     * @dataProvider providerBinaryToIp
     */
    public function testBinaryToIp(string $expected, string $input): void
    {
        $result = FormatConverter::binaryToIp($input);
        $this->assertEquals($expected, $result);
    }

    /**
     * Data provider for binaroToIp
     *
     * @return array
     */
    public function providerBinaryToIp(): array
    {
        return [
            [
                '10.11.12.13',
                '0x0a0b0c0d',
            ],
            [
                'my ip',
                'my ip',
            ],
        ];
    }

    /**
     * Test for ipToBinary
     *
     * @param string $expected Expected result given an input
     * @param string $input    Input to convert
     *
     * @return void
     *
     * @dataProvider providerIpToBinary
     */
    public function testIpToBinary(string $expected, string $input): void
    {
        $result = FormatConverter::ipToBinary($input);
        $this->assertEquals($expected, $result);
    }

    /**
     * Data provider for ipToBinary
     *
     * @return array
     */
    public function providerIpToBinary(): array
    {
        return [
            [
                '0x0a0b0c0d',
                '10.11.12.13',
            ],
            [
                'my ip',
                'my ip',
            ],
        ];
    }

    /**
     * Test for ipToLong
     *
     * @param string $expected Expected result given an input
     * @param string $input    Input to convert
     *
     * @return void
     *
     * @dataProvider providerIpToLong
     */
    public function testIpToLong(string $expected, string $input): void
    {
        $result = FormatConverter::ipToLong($input);
        $this->assertEquals($expected, $result);
    }

    /**
     * Data provider for ipToLong
     *
     * @return array
     */
    public function providerIpToLong(): array
    {
        return [
            [
                '168496141',
                '10.11.12.13',
            ],
            [
                'my ip',
                'my ip',
            ],
        ];
    }

    /**
     * Test for longToIp
     *
     * @param string $expected Expected result given an input
     * @param string $input    Input to convert
     *
     * @return void
     *
     * @dataProvider providerLongToIp
     */
    public function testLongToIp(string $expected, string $input): void
    {
        $result = FormatConverter::longToIp($input);
        $this->assertEquals($expected, $result);
    }

    /**
     * Data provider for longToIp
     *
     * @return array
     */
    public function providerLongToIp(): array
    {
        return [
            [
                '10.11.12.13',
                '168496141',
            ],
            [
                'my ip',
                'my ip',
            ],
        ];
    }
}
