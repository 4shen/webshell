<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Rector\Property\InjectAnnotationClassRector;

use DI\Annotation\Inject as PHPDIInject;
use Iterator;
use JMS\DiExtraBundle\Annotation\Inject;
use Rector\Core\Configuration\Option;
use Rector\Core\Rector\Property\InjectAnnotationClassRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class InjectAnnotationClassRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $file): void
    {
        $this->setParameter(
            Option::SYMFONY_CONTAINER_XML_PATH_PARAMETER,
            __DIR__ . '/../../../../rules/symfony/tests/Rector/FrameworkBundle/GetToConstructorInjectionRector/xml/services.xml'
        );

        $this->doTestFileInfo($file);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            InjectAnnotationClassRector::class => [
                '$annotationClasses' => [Inject::class, PHPDIInject::class],
            ],
        ];
    }
}
