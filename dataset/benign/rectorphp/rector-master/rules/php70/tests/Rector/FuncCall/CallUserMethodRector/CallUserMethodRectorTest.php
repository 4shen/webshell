<?php

declare(strict_types=1);

namespace Rector\Php70\Tests\Rector\FuncCall\CallUserMethodRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Php70\Rector\FuncCall\CallUserMethodRector;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @see https://www.mail-archive.com/php-dev@lists.php.net/msg11576.html
 */
final class CallUserMethodRectorTest extends AbstractRectorTestCase
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
        return CallUserMethodRector::class;
    }
}
