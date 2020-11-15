<?php

declare(strict_types=1);

namespace Rector\Php70\Tests\Rector\FunctionLike\ExceptionHandlerTypehintRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Php70\Rector\FunctionLike\ExceptionHandlerTypehintRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ExceptionHandlerTypehintRectorTest extends AbstractRectorTestCase
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
        return ExceptionHandlerTypehintRector::class;
    }
}
