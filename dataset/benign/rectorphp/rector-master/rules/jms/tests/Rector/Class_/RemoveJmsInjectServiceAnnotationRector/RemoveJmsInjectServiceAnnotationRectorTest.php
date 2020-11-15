<?php

declare(strict_types=1);

namespace Rector\JMS\Tests\Rector\Class_\RemoveJmsInjectServiceAnnotationRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\JMS\Rector\Class_\RemoveJmsInjectServiceAnnotationRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RemoveJmsInjectServiceAnnotationRectorTest extends AbstractRectorTestCase
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
        return RemoveJmsInjectServiceAnnotationRector::class;
    }
}
