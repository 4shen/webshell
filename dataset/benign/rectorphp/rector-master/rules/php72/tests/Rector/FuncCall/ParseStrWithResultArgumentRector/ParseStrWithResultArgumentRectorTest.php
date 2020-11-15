<?php

declare(strict_types=1);

namespace Rector\Php72\Tests\Rector\FuncCall\ParseStrWithResultArgumentRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Php72\Rector\FuncCall\ParseStrWithResultArgumentRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ParseStrWithResultArgumentRectorTest extends AbstractRectorTestCase
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
        return ParseStrWithResultArgumentRector::class;
    }
}
