<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Tests\Rector\DelegateExceptionArgumentsRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\PHPUnit\Rector\DelegateExceptionArgumentsRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class DelegateExceptionArgumentsRectorTest extends AbstractRectorTestCase
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
        return DelegateExceptionArgumentsRector::class;
    }
}
