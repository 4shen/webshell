<?php

declare(strict_types=1);

namespace Rector\Php73\Tests\Rector\ConstFetch\SensitiveConstantNameRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Php73\Rector\ConstFetch\SensitiveConstantNameRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class SensitiveConstantNameRectorTest extends AbstractRectorTestCase
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
        return SensitiveConstantNameRector::class;
    }
}
