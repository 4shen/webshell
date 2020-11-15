<?php

declare(strict_types=1);

namespace Rector\Nette\Tests\Rector\FuncCall\SubstrStrlenFunctionToNetteUtilsStringsRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Nette\Rector\FuncCall\SubstrStrlenFunctionToNetteUtilsStringsRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class SubstrStrlenFunctionToNetteUtilsStringsRectorTest extends AbstractRectorTestCase
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
        return SubstrStrlenFunctionToNetteUtilsStringsRector::class;
    }
}
