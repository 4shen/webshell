<?php

declare(strict_types=1);

namespace Rector\Php70\Tests\Rector\Break_\BreakNotInLoopOrSwitchToReturnRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Php70\Rector\Break_\BreakNotInLoopOrSwitchToReturnRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class BreakNotInLoopOrSwitchToReturnRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $file): void
    {
        $this->doTestFileInfoWithoutAutoload($file);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorClass(): string
    {
        return BreakNotInLoopOrSwitchToReturnRector::class;
    }
}
