<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Tests\Rector\Class_\ArrayArgumentInTestToDataProviderRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\PHPUnit\Rector\Class_\ArrayArgumentInTestToDataProviderRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ArrayArgumentInTestToDataProviderRectorTest extends AbstractRectorTestCase
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
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            ArrayArgumentInTestToDataProviderRector::class => [
                '$configuration' => [
                    [
                        'class' => 'PHPUnit\Framework\TestCase',
                        'old_method' => 'doTestMultiple',
                        'new_method' => 'doTestSingle',
                        'variable_name' => 'variable',
                    ],
                ],
            ],
        ];
    }
}
