<?php

declare(strict_types=1);

namespace Rector\Php73\Tests\Rector\BinaryOp\IsCountableRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Php73\Rector\BinaryOp\IsCountableRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class IsCountableRectorTest extends AbstractRectorTestCase
{
    /**
     * @requires PHP >= 7.3
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
        return IsCountableRector::class;
    }
}
