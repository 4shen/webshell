<?php

declare(strict_types=1);

namespace Rector\Php53\Tests\Rector\Ternary\TernaryToElvisRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Php53\Rector\Ternary\TernaryToElvisRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class TernaryToElvisRectorTest extends AbstractRectorTestCase
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
        return TernaryToElvisRector::class;
    }
}
