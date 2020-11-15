<?php

declare(strict_types=1);

namespace Rector\Php53\Tests\Rector\Assign\ClearReturnNewByReferenceRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Php53\Rector\Assign\ClearReturnNewByReferenceRector;
use SplFileInfo;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ClearReturnNewByReferenceRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(SmartFileInfo $file): void
    {
        $this->doTestFileInfoWithoutAutoload($file);
    }

    /**
     * @return Iterator<SplFileInfo>
     */
    public function provideDataForTest(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return ClearReturnNewByReferenceRector::class;
    }
}
