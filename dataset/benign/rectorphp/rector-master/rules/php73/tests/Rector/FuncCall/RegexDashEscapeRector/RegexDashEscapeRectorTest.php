<?php

declare(strict_types=1);

namespace Rector\Php73\Tests\Rector\FuncCall\RegexDashEscapeRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Php73\Rector\FuncCall\RegexDashEscapeRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RegexDashEscapeRectorTest extends AbstractRectorTestCase
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
        return RegexDashEscapeRector::class;
    }
}
