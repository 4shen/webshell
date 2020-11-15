<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\PhpSpecToPHPUnitRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\PhpSpecToPHPUnit\Rector\Class_\AddMockPropertiesRector;
use Rector\PhpSpecToPHPUnit\Rector\Class_\PhpSpecClassToPHPUnitClassRector;
use Rector\PhpSpecToPHPUnit\Rector\ClassMethod\MockVariableToPropertyFetchRector;
use Rector\PhpSpecToPHPUnit\Rector\ClassMethod\PhpSpecMethodToPHPUnitMethodRector;
use Rector\PhpSpecToPHPUnit\Rector\MethodCall\PhpSpecMocksToPHPUnitMocksRector;
use Rector\PhpSpecToPHPUnit\Rector\MethodCall\PhpSpecPromisesToPHPUnitAssertRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class PhpSpecToPHPUnitRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $file): void
    {
        $this->doTestFileInfo($file);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    /**
     * @return string[][]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            # 1. first convert mocks
            PhpSpecMocksToPHPUnitMocksRector::class => [],
            PhpSpecPromisesToPHPUnitAssertRector::class => [],
            PhpSpecMethodToPHPUnitMethodRector::class => [],
            PhpSpecClassToPHPUnitClassRector::class => [],
            AddMockPropertiesRector::class => [],
            MockVariableToPropertyFetchRector::class => [],
        ];
    }
}
