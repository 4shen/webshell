<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\FuncCall\CallUserFuncCallToVariadicRector;

use Iterator;
use Rector\CodingStyle\Rector\FuncCall\CallUserFuncCallToVariadicRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class CallUserFuncCallToVariadicRectorTest extends AbstractRectorTestCase
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
        return CallUserFuncCallToVariadicRector::class;
    }
}
