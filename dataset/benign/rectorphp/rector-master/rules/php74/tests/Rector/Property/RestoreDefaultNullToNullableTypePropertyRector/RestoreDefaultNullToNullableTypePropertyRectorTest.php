<?php

declare(strict_types=1);

namespace Rector\Php74\Tests\Rector\Property\RestoreDefaultNullToNullableTypePropertyRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Php74\Rector\Property\RestoreDefaultNullToNullableTypePropertyRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RestoreDefaultNullToNullableTypePropertyRectorTest extends AbstractRectorTestCase
{
    /**
     * @requires PHP >= 7.4
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
        return RestoreDefaultNullToNullableTypePropertyRector::class;
    }
}
