<?php

declare(strict_types=1);

namespace Rector\Php70\Tests\Rector\StaticCall\StaticCallOnNonStaticToInstanceCallRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Php70\Rector\StaticCall\StaticCallOnNonStaticToInstanceCallRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class StaticCallOnNonStaticToInstanceCallRectorTest extends AbstractRectorTestCase
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
        return StaticCallOnNonStaticToInstanceCallRector::class;
    }
}
