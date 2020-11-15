<?php

declare(strict_types=1);

namespace Rector\Php73\Tests\Rector\FuncCall\StringifyStrNeedlesRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Php73\Rector\FuncCall\StringifyStrNeedlesRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class StringifyStrNeedlesRectorTest extends AbstractRectorTestCase
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
        return StringifyStrNeedlesRector::class;
    }
}
