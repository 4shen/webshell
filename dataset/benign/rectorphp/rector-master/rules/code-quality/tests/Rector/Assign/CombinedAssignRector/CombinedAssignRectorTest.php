<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Tests\Rector\Assign\CombinedAssignRector;

use Iterator;
use Rector\CodeQuality\Rector\Assign\CombinedAssignRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * Some tests used from:
 * - https://github.com/doctrine/coding-standard/pull/83/files
 * - https://github.com/slevomat/coding-standard/blob/master/tests/Sniffs/Operators/data/requireCombinedAssignmentOperatorErrors.php
 */
final class CombinedAssignRectorTest extends AbstractRectorTestCase
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
        return CombinedAssignRector::class;
    }
}
