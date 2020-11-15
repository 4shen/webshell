<?php
/**
 * Abstract parent class for all Gis<Geom_type> test classes
 */

declare(strict_types=1);

namespace PhpMyAdmin\Tests\Gis;

use PhpMyAdmin\Tests\AbstractTestCase;
use function imagesx;

/**
 * Abstract parent class for all Gis<Geom_type> test classes
 */
abstract class GisGeomTestCase extends AbstractTestCase
{
    protected $object;

    /**
     * test generateParams method
     *
     * @param string $wkt    point in WKT form
     * @param int    $index  index
     * @param array  $params expected output array
     *
     * @dataProvider providerForTestGenerateParams
     */
    public function testGenerateParams($wkt, $index, $params): void
    {
        if ($index == null) {
            $this->assertEquals(
                $params,
                $this->object->generateParams($wkt)
            );
        } else {
            $this->assertEquals(
                $params,
                $this->object->generateParams($wkt, $index)
            );
        }
    }

    /**
     * test scaleRow method
     *
     * @param string $spatial spatial data of a row
     * @param array  $min_max expected results
     *
     * @dataProvider providerForTestScaleRow
     */
    public function testScaleRow($spatial, $min_max): void
    {
        $this->assertEquals(
            $min_max,
            $this->object->scaleRow($spatial)
        );
    }

    /**
     * Tests whether content is a valid image.
     *
     * @param resource $object Image
     *
     * @return void
     */
    public function assertImage($object)
    {
        $this->assertGreaterThan(0, imagesx($object));
    }
}
