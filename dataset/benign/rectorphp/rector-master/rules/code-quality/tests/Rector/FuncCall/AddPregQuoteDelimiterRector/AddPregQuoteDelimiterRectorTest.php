<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\FuncCall\AddPregQuoteDelimiterRector;

use Iterator;
use Rector\CodeQuality\Rector\FuncCall\AddPregQuoteDelimiterRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class AddPregQuoteDelimiterRectorTest extends AbstractRectorTestCase
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
        return AddPregQuoteDelimiterRector::class;
    }
}
