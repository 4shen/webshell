<?php

declare(strict_types=1);

namespace Rector\CakePHP\Tests\Rector\Name\ImplicitShortClassNameUseStatementRector;

use Iterator;
use Rector\CakePHP\Rector\Name\ImplicitShortClassNameUseStatementRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ImplicitShortClassNameUseStatementRectorTest extends AbstractRectorTestCase
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
        return ImplicitShortClassNameUseStatementRector::class;
    }
}
