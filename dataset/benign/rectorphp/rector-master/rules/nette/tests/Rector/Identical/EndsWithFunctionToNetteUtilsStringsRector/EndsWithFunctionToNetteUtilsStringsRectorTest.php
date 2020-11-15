<?php

declare(strict_types=1);

namespace Rector\Nette\Tests\Rector\Identical\EndsWithFunctionToNetteUtilsStringsRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Nette\Rector\Identical\EndsWithFunctionToNetteUtilsStringsRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class EndsWithFunctionToNetteUtilsStringsRectorTest extends AbstractRectorTestCase
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
        return EndsWithFunctionToNetteUtilsStringsRector::class;
    }
}
