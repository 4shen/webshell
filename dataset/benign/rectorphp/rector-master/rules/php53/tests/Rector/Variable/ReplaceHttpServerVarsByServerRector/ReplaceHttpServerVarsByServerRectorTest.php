<?php

declare(strict_types=1);

namespace Rector\Php53\Tests\Rector\Variable\ReplaceHttpServerVarsByServerRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Php53\Rector\Variable\ReplaceHttpServerVarsByServerRector;
use SplFileInfo;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ReplaceHttpServerVarsByServerRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(SmartFileInfo $file): void
    {
        $this->doTestFileInfo($file);
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
        return ReplaceHttpServerVarsByServerRector::class;
    }
}
