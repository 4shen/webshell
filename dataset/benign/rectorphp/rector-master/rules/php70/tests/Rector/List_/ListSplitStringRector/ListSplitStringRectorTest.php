<?php

declare(strict_types=1);

namespace Rector\Php70\Tests\Rector\List_\ListSplitStringRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Php70\Rector\List_\ListSplitStringRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ListSplitStringRectorTest extends AbstractRectorTestCase
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
        return ListSplitStringRector::class;
    }
}
