<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Exclusion\Check;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\DeadCode\Rector\ClassMethod\RemoveEmptyClassMethodRector;
use Rector\DeadCode\Rector\Plus\RemoveDeadZeroAndOneOperationRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ExcludeByDocBlockExclusionCheckTest extends AbstractRectorTestCase
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
            RemoveEmptyClassMethodRector::class => [],
            RemoveDeadZeroAndOneOperationRector::class => [],
        ];
    }
}
