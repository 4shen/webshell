<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\If_\RemoveAlwaysTrueIfConditionRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\DeadCode\Rector\If_\RemoveAlwaysTrueIfConditionRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RemoveAlwaysTrueIfConditionRectorTest extends AbstractRectorTestCase
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

    protected function getRectorClass(): string
    {
        return RemoveAlwaysTrueIfConditionRector::class;
    }
}
