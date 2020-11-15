<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\If_\ShortenElseIfRector;

use Iterator;
use Rector\CodeQuality\Rector\If_\ShortenElseIfRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ShortenElseIfRectorTest extends AbstractRectorTestCase
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
        return ShortenElseIfRector::class;
    }
}
