<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\Class_\AddArrayDefaultToArrayPropertyRector;

use Iterator;
use Rector\CodingStyle\Rector\Class_\AddArrayDefaultToArrayPropertyRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class AddArrayDefaultToArrayPropertyRectorTest extends AbstractRectorTestCase
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
        return AddArrayDefaultToArrayPropertyRector::class;
    }
}
