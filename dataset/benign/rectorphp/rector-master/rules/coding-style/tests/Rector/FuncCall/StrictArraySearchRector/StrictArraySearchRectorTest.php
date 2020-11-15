<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\FuncCall\StrictArraySearchRector;

use Iterator;
use Rector\CodingStyle\Rector\FuncCall\StrictArraySearchRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class StrictArraySearchRectorTest extends AbstractRectorTestCase
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
        return StrictArraySearchRector::class;
    }
}
