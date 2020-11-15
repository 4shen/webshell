<?php

declare(strict_types=1);

namespace Rector\Php70\Tests\Rector\FunctionLike\Php4ConstructorRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Php70\Rector\FunctionLike\Php4ConstructorRector;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * Some test cases used from:
 * - https://github.com/FriendsOfPHP/PHP-CS-Fixer/blob/2.12/tests/Fixer/ClassNotation/NoPhp4ConstructorFixerTest.php
 */
final class Php4ConstructorRectorTest extends AbstractRectorTestCase
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
        return Php4ConstructorRector::class;
    }
}
