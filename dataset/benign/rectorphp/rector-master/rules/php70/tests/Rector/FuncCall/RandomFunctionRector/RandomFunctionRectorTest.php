<?php

declare(strict_types=1);

namespace Rector\Php70\Tests\Rector\FuncCall\RandomFunctionRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Php70\Rector\FuncCall\RandomFunctionRector;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * Some tests copied from https://github.com/FriendsOfPHP/PHP-CS-Fixer/blob/2.12/tests/Fixer/Alias/RandomApiMigrationFixerTest.php
 */
final class RandomFunctionRectorTest extends AbstractRectorTestCase
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
        return RandomFunctionRector::class;
    }
}
