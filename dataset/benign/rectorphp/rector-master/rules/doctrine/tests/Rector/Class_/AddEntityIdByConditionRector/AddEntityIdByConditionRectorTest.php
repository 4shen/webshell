<?php

declare(strict_types=1);

namespace Rector\Doctrine\Tests\Rector\Class_\AddEntityIdByConditionRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Doctrine\Rector\Class_\AddEntityIdByConditionRector;
use Rector\Doctrine\Tests\Rector\Class_\AddEntityIdByConditionRector\Source\SomeTrait;
use Symplify\SmartFileSystem\SmartFileInfo;

final class AddEntityIdByConditionRectorTest extends AbstractRectorTestCase
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

    protected function getRectorsWithConfiguration(): array
    {
        return [
            AddEntityIdByConditionRector::class => [
                '$detectedTraits' => [SomeTrait::class],
            ],
        ];
    }
}
