<?php

declare(strict_types=1);

namespace Rector\Php73\Tests\Rector\FuncCall\RemoveMissingCompactVariableRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Php73\Rector\FuncCall\RemoveMissingCompactVariableRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RemoveMissingCompactVariableRectorTest extends AbstractRectorTestCase
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
        return RemoveMissingCompactVariableRector::class;
    }
}
