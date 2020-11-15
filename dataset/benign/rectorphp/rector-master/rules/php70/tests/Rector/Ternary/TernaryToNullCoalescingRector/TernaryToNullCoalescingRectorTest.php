<?php

declare(strict_types=1);

namespace Rector\Php70\Tests\Rector\Ternary\TernaryToNullCoalescingRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Php70\Rector\Ternary\TernaryToNullCoalescingRector;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * Some tests copied from:
 * https://github.com/FriendsOfPHP/PHP-CS-Fixer/commit/0db4f91088a3888a7c8b26e5a36fba53c0d9507c#diff-02f477b178d0dc5b25ac05ab3b59e7c7
 * https://github.com/slevomat/coding-standard/blob/master/tests/Sniffs/ControlStructures/data/requireNullCoalesceOperatorErrors.fixed.php
 */
final class TernaryToNullCoalescingRectorTest extends AbstractRectorTestCase
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
        return TernaryToNullCoalescingRector::class;
    }
}
