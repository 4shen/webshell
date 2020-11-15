<?php

declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\FrameworkBundle\GetToConstructorInjectionRector;

use Iterator;
use Rector\Core\Configuration\Option;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Symfony\Rector\FrameworkBundle\GetToConstructorInjectionRector;
use Rector\Symfony\Tests\Rector\FrameworkBundle\GetToConstructorInjectionRector\Source\GetTrait;
use Rector\Symfony\Tests\Rector\Source\SymfonyController;
use Symplify\SmartFileSystem\SmartFileInfo;

final class GetToConstructorInjectionRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $file): void
    {
        $this->setParameter(Option::SYMFONY_CONTAINER_XML_PATH_PARAMETER, __DIR__ . '/xml/services.xml');
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
            GetToConstructorInjectionRector::class => [
                '$getMethodAwareTypes' => [SymfonyController::class, GetTrait::class],
            ],
        ];
    }
}
