<?php
/**
 * Test for PhpMyAdmin\Gis\GisPoint
 */

declare(strict_types=1);

namespace PhpMyAdmin\Tests\Gis;

use PhpMyAdmin\Gis\GisPoint;
use TCPDF;
use function function_exists;
use function imagecreatetruecolor;

/**
 * Tests for PhpMyAdmin\Gis\GisPoint class.
 */
class GisPointTest extends GisGeomTestCase
{
    /**
     * @var    GisPoint
     * @access protected
     */
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
        $this->object = GisPoint::singleton();
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
     * data provider for testGenerateWkt
     *
     * @return array data for testGenerateWkt
     */
    public function providerForTestGenerateWkt()
    {
        return [
            [
                [
                    0 => [
                        'POINT' => [
                            'x' => 5.02,
                            'y' => 8.45,
                        ],
                    ],
                ],
                0,
                null,
                'POINT(5.02 8.45)',
            ],
            [
                [
                    0 => [
                        'POINT' => [
                            'x' => 5.02,
                            'y' => 8.45,
                        ],
                    ],
                ],
                1,
                null,
                'POINT( )',
            ],
            [
                [0 => ['POINT' => ['x' => 5.02]]],
                0,
                null,
                'POINT(5.02 )',
            ],
            [
                [0 => ['POINT' => ['y' => 8.45]]],
                0,
                null,
                'POINT( 8.45)',
            ],
            [
                [0 => ['POINT' => []]],
                0,
                null,
                'POINT( )',
            ],
        ];
    }

    /**
     * test getShape method
     *
     * @param array  $row_data array of GIS data
     * @param string $shape    expected shape in WKT
     *
     * @dataProvider providerForTestGetShape
     */
    public function testGetShape($row_data, $shape): void
    {
        $this->assertEquals($this->object->getShape($row_data), $shape);
    }

    /**
     * data provider for testGetShape
     *
     * @return array data for testGetShape
     */
    public function providerForTestGetShape()
    {
        return [
            [
                [
                    'x' => 5.02,
                    'y' => 8.45,
                ],
                'POINT(5.02 8.45)',
            ],
        ];
    }

    /**
     * data provider for testGenerateParams
     *
     * @return array data for testGenerateParams
     */
    public function providerForTestGenerateParams()
    {
        return [
            [
                "'POINT(5.02 8.45)',124",
                null,
                [
                    'srid' => '124',
                    0      => [
                        'POINT'    => [
                            'x' => '5.02',
                            'y' => '8.45',
                        ],
                    ],
                ],
            ],
            [
                'POINT(5.02 8.45)',
                2,
                [
                    2 => [
                        'gis_type' => 'POINT',
                        'POINT'    => [
                            'x' => '5.02',
                            'y' => '8.45',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * data provider for testScaleRow
     *
     * @return array data for testScaleRow
     */
    public function providerForTestScaleRow()
    {
        return [
            [
                'POINT(12 35)',
                [
                    'minX' => 12,
                    'maxX' => 12,
                    'minY' => 35,
                    'maxY' => 35,
                ],
            ],
        ];
    }

    /**
     * test case for prepareRowAsPng() method
     *
     * @param string   $spatial     GIS POINT object
     * @param string   $label       label for the GIS POINT object
     * @param string   $point_color color for the GIS POINT object
     * @param array    $scale_data  array containing data related to scaling
     * @param resource $image       image object
     *
     * @return void
     *
     * @dataProvider providerForPrepareRowAsPng
     */
    public function testPrepareRowAsPng(
        $spatial,
        $label,
        $point_color,
        $scale_data,
        $image
    ) {
        $return = $this->object->prepareRowAsPng(
            $spatial,
            $label,
            $point_color,
            $scale_data,
            $image
        );
        $this->assertImage($return);
    }

    /**
     * data provider for testPrepareRowAsPng() test case
     *
     * @return array test data for testPrepareRowAsPng() test case
     */
    public function providerForPrepareRowAsPng()
    {
        if (! function_exists('imagecreatetruecolor')) {
            $this->markTestSkipped('GD extension missing!');
        }

        return [
            [
                'POINT(12 35)',
                'image',
                '#B02EE0',
                [
                    'x' => 12,
                    'y' => 69,
                    'scale' => 2,
                    'height' => 150,
                ],
                imagecreatetruecolor(120, 150),
            ],
        ];
    }

    /**
     * test case for prepareRowAsPdf() method
     *
     * @param string $spatial     GIS POINT object
     * @param string $label       label for the GIS POINT object
     * @param string $point_color color for the GIS POINT object
     * @param array  $scale_data  array containing data related to scaling
     * @param TCPDF  $pdf         TCPDF instance
     *
     * @return void
     *
     * @dataProvider providerForPrepareRowAsPdf
     */
    public function testPrepareRowAsPdf(
        $spatial,
        $label,
        $point_color,
        $scale_data,
        $pdf
    ) {
        $return = $this->object->prepareRowAsPdf(
            $spatial,
            $label,
            $point_color,
            $scale_data,
            $pdf
        );
        $this->assertInstanceOf('TCPDF', $return);
    }

    /**
     * data provider for prepareRowAsPdf() test case
     *
     * @return array test data for prepareRowAsPdf() test case
     */
    public function providerForPrepareRowAsPdf()
    {
        return [
            [
                'POINT(12 35)',
                'pdf',
                '#B02EE0',
                [
                    'x' => 12,
                    'y' => 69,
                    'scale' => 2,
                    'height' => 150,
                ],
                new TCPDF(),
            ],
        ];
    }

    /**
     * test case for prepareRowAsSvg() method
     *
     * @param string $spatial    GIS POINT object
     * @param string $label      label for the GIS POINT object
     * @param string $pointColor color for the GIS POINT object
     * @param array  $scaleData  array containing data related to scaling
     * @param string $output     expected output
     *
     * @return void
     *
     * @dataProvider providerForPrepareRowAsSvg
     */
    public function testPrepareRowAsSvg(
        $spatial,
        $label,
        $pointColor,
        $scaleData,
        $output
    ) {
        $this->assertEquals(
            $output,
            $this->object->prepareRowAsSvg(
                $spatial,
                $label,
                $pointColor,
                $scaleData
            )
        );
    }

    /**
     * data provider for prepareRowAsSvg() test case
     *
     * @return array test data for prepareRowAsSvg() test case
     */
    public function providerForPrepareRowAsSvg()
    {
        return [
            [
                'POINT(12 35)',
                'svg',
                '#B02EE0',
                [
                    'x' => 12,
                    'y' => 69,
                    'scale' => 2,
                    'height' => 150,
                ],
                '',
            ],
        ];
    }

    /**
     * test case for prepareRowAsOl() method
     *
     * @param string $spatial     GIS POINT object
     * @param int    $srid        spatial reference ID
     * @param string $label       label for the GIS POINT object
     * @param string $point_color color for the GIS POINT object
     * @param array  $scale_data  array containing data related to scaling
     * @param string $output      expected output
     *
     * @return void
     *
     * @dataProvider providerForPrepareRowAsOl
     */
    public function testPrepareRowAsOl(
        $spatial,
        $srid,
        $label,
        $point_color,
        $scale_data,
        $output
    ) {
        $this->assertEquals(
            $output,
            $this->object->prepareRowAsOl(
                $spatial,
                $srid,
                $label,
                $point_color,
                $scale_data
            )
        );
    }

    /**
     * data provider for testPrepareRowAsOl() test case
     *
     * @return array test data for testPrepareRowAsOl() test case
     */
    public function providerForPrepareRowAsOl()
    {
        return [
            [
                'POINT(12 35)',
                4326,
                'Ol',
                '#B02EE0',
                [
                    'minX' => '0',
                    'minY' => '0',
                    'maxX' => '1',
                    'maxY' => '1',
                ],
                'bound = new OpenLayers.Bounds(); bound.extend(new OpenLayers.'
                . 'LonLat(0, 0).transform(new OpenLayers.Projection("EPSG:4326"), '
                . 'map.getProjectionObject())); bound.extend(new OpenLayers.LonLat'
                . '(1, 1).transform(new OpenLayers.Projection("EPSG:4326"), '
                . 'map.getProjectionObject()));vectorLayer.addFeatures(new Open'
                . 'Layers.Feature.Vector((new OpenLayers.Geometry.Point(12,35)).'
                . 'transform(new OpenLayers.Projection("EPSG:4326"), map.get'
                . 'ProjectionObject()), null, {"pointRadius":3,"fillColor":"#ffffff"'
                . ',"strokeColor":"#B02EE0","strokeWidth":2,"label":"Ol","labelY'
                . 'Offset":-8,"fontSize":10}));',
            ],
        ];
    }
}
