<?php
/**
 * tests for PhpMyAdmin\Plugins\Export\ExportPdf class
 */

declare(strict_types=1);

namespace PhpMyAdmin\Tests\Plugins\Export;

use PhpMyAdmin\Plugins\Export\ExportPdf;
use PhpMyAdmin\Plugins\Export\Helpers\Pdf;
use PhpMyAdmin\Properties\Options\Groups\OptionsPropertyMainGroup;
use PhpMyAdmin\Properties\Options\Groups\OptionsPropertyRootGroup;
use PhpMyAdmin\Properties\Options\Items\RadioPropertyItem;
use PhpMyAdmin\Properties\Options\Items\TextPropertyItem;
use PhpMyAdmin\Properties\Plugins\ExportPluginProperties;
use PhpMyAdmin\Tests\AbstractTestCase;
use ReflectionMethod;
use ReflectionProperty;
use function array_shift;

/**
 * tests for PhpMyAdmin\Plugins\Export\ExportPdf class
 *
 * @group medium
 */
class ExportPdfTest extends AbstractTestCase
{
    protected $object;

    /**
     * Configures global environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        parent::defineVersionConstants();
        $GLOBALS['server'] = 0;
        $GLOBALS['output_kanji_conversion'] = false;
        $GLOBALS['output_charset_conversion'] = false;
        $GLOBALS['buffer_needed'] = false;
        $GLOBALS['asfile'] = true;
        $GLOBALS['save_on_server'] = false;
        $this->object = new ExportPdf();
    }

    /**
     * tearDown for test cases
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        unset($this->object);
    }

    /**
     * Test for PhpMyAdmin\Plugins\Export\ExportPdf::setProperties
     *
     * @return void
     */
    public function testSetProperties()
    {
        $method = new ReflectionMethod(ExportPdf::class, 'setProperties');
        $method->setAccessible(true);
        $method->invoke($this->object, null);

        $attrProperties = new ReflectionProperty(ExportPdf::class, 'properties');
        $attrProperties->setAccessible(true);
        $properties = $attrProperties->getValue($this->object);

        $this->assertInstanceOf(
            ExportPluginProperties::class,
            $properties
        );

        $this->assertEquals(
            'PDF',
            $properties->getText()
        );

        $this->assertEquals(
            'pdf',
            $properties->getExtension()
        );

        $this->assertEquals(
            'application/pdf',
            $properties->getMimeType()
        );

        $this->assertEquals(
            'Options',
            $properties->getOptionsText()
        );

        $this->assertTrue(
            $properties->getForceFile()
        );

        $options = $properties->getOptions();

        $this->assertInstanceOf(
            OptionsPropertyRootGroup::class,
            $options
        );

        $this->assertEquals(
            'Format Specific Options',
            $options->getName()
        );

        $generalOptionsArray = $options->getProperties();

        $generalOptions = array_shift($generalOptionsArray);

        $this->assertInstanceOf(
            OptionsPropertyMainGroup::class,
            $generalOptions
        );

        $this->assertEquals(
            'general_opts',
            $generalOptions->getName()
        );

        $generalProperties = $generalOptions->getProperties();

        $property = array_shift($generalProperties);

        $this->assertInstanceOf(
            TextPropertyItem::class,
            $property
        );

        $this->assertEquals(
            'report_title',
            $property->getName()
        );

        $generalOptions = array_shift($generalOptionsArray);

        $this->assertInstanceOf(
            OptionsPropertyMainGroup::class,
            $generalOptions
        );

        $this->assertEquals(
            'dump_what',
            $generalOptions->getName()
        );

        $this->assertEquals(
            'Dump table',
            $generalOptions->getText()
        );

        $generalProperties = $generalOptions->getProperties();

        $property = array_shift($generalProperties);

        $this->assertInstanceOf(
            RadioPropertyItem::class,
            $property
        );

        $this->assertEquals(
            'structure_or_data',
            $property->getName()
        );

        $this->assertEquals(
            [
                'structure' => __('structure'),
                'data' => __('data'),
                'structure_and_data' => __('structure and data'),
            ],
            $property->getValues()
        );
    }

    /**
     * Test for PhpMyAdmin\Plugins\Export\ExportPdf::exportHeader
     *
     * @return void
     */
    public function testExportHeader()
    {
        $pdf = $this->getMockBuilder(Pdf::class)
            ->disableOriginalConstructor()
            ->getMock();

        $pdf->expects($this->once())
            ->method('Open');

        $pdf->expects($this->once())
            ->method('setAttributes');

        $pdf->expects($this->once())
            ->method('setTopMargin');

        $attrPdf = new ReflectionProperty(ExportPdf::class, '_pdf');
        $attrPdf->setAccessible(true);
        $attrPdf->setValue($this->object, $pdf);

        $this->assertTrue(
            $this->object->exportHeader()
        );
    }

    /**
     * Test for PhpMyAdmin\Plugins\Export\ExportPdf::exportFooter
     *
     * @return void
     */
    public function testExportFooter()
    {
        $pdf = $this->getMockBuilder(Pdf::class)
            ->disableOriginalConstructor()
            ->getMock();

        $pdf->expects($this->once())
            ->method('getPDFData');

        $attrPdf = new ReflectionProperty(ExportPdf::class, '_pdf');
        $attrPdf->setAccessible(true);
        $attrPdf->setValue($this->object, $pdf);

        $this->assertTrue(
            $this->object->exportFooter()
        );
    }

    /**
     * Test for PhpMyAdmin\Plugins\Export\ExportPdf::exportDBHeader
     *
     * @return void
     */
    public function testExportDBHeader()
    {
        $this->assertTrue(
            $this->object->exportDBHeader('testDB')
        );
    }

    /**
     * Test for PhpMyAdmin\Plugins\Export\ExportPdf::exportDBFooter
     *
     * @return void
     */
    public function testExportDBFooter()
    {
        $this->assertTrue(
            $this->object->exportDBFooter('testDB')
        );
    }

    /**
     * Test for PhpMyAdmin\Plugins\Export\ExportPdf::exportDBCreate
     *
     * @return void
     */
    public function testExportDBCreate()
    {
        $this->assertTrue(
            $this->object->exportDBCreate('testDB', 'database')
        );
    }

    /**
     * Test for PhpMyAdmin\Plugins\Export\ExportPdf::exportData
     *
     * @return void
     */
    public function testExportData()
    {
        $pdf = $this->getMockBuilder(Pdf::class)
            ->disableOriginalConstructor()
            ->getMock();

        $pdf->expects($this->once())
            ->method('setAttributes')
            ->with(
                [
                    'currentDb' => 'db',
                    'currentTable' => 'table',
                    'dbAlias' => 'db',
                    'tableAlias' => 'table',
                    'aliases' => [],
                    'purpose' => __('Dumping data'),
                ]
            );

        $pdf->expects($this->once())
            ->method('mysqlReport')
            ->with('SELECT');

        $attrPdf = new ReflectionProperty(ExportPdf::class, '_pdf');
        $attrPdf->setAccessible(true);
        $attrPdf->setValue($this->object, $pdf);

        $this->assertTrue(
            $this->object->exportData(
                'db',
                'table',
                "\n",
                'phpmyadmin.net/err',
                'SELECT'
            )
        );
    }

    /**
     * Test for
     *     - PhpMyAdmin\Plugins\Export\ExportPdf::setPdf
     *     - PhpMyAdmin\Plugins\Export\ExportPdf::getPdf
     *
     * @return void
     */
    public function testSetGetPdf()
    {
        $setter = new ReflectionMethod(ExportPdf::class, 'setPdf');
        $setter->setAccessible(true);
        $setter->invoke($this->object, new Pdf());

        $getter = new ReflectionMethod(ExportPdf::class, 'getPdf');
        $getter->setAccessible(true);
        $this->assertInstanceOf(
            Pdf::class,
            $getter->invoke($this->object)
        );
    }

    /**
     * Test for
     *     - PhpMyAdmin\Plugins\Export\ExportPdf::setPdfReportTitle
     *     - PhpMyAdmin\Plugins\Export\ExportPdf::getPdfReportTitle
     *
     * @return void
     */
    public function testSetGetPdfTitle()
    {
        $setter = new ReflectionMethod(ExportPdf::class, 'setPdfReportTitle');
        $setter->setAccessible(true);
        $setter->invoke($this->object, 'title');

        $getter = new ReflectionMethod(ExportPdf::class, 'getPdfReportTitle');
        $getter->setAccessible(true);
        $this->assertEquals(
            'title',
            $getter->invoke($this->object)
        );
    }
}
