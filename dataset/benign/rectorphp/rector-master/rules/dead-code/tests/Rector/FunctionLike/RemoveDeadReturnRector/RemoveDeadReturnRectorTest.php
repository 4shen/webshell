<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\FunctionLike\RemoveDeadReturnRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\DeadCode\Rector\FunctionLike\RemoveDeadReturnRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RemoveDeadReturnRectorTest extends AbstractRectorTestCase
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
        return RemoveDeadReturnRector::class;
    }
}
