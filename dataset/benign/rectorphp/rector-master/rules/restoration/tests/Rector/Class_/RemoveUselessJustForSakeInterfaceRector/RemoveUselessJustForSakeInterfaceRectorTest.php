<?php

declare(strict_types=1);

namespace Rector\Restoration\Tests\Rector\Class_\RemoveUselessJustForSakeInterfaceRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Restoration\Rector\Class_\RemoveUselessJustForSakeInterfaceRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RemoveUselessJustForSakeInterfaceRectorTest extends AbstractRectorTestCase
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
        return RemoveUselessJustForSakeInterfaceRector::class;
    }
}
