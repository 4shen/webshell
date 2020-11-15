<?php

declare(strict_types=1);

namespace Rector\MockistaToMockery\Tests\Rector\ClassMethod\MockistaMockToMockeryMockRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\MockistaToMockery\Rector\ClassMethod\MockistaMockToMockeryMockRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class MockistaMockToMockeryMockRectorTest extends AbstractRectorTestCase
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
        return MockistaMockToMockeryMockRector::class;
    }
}
