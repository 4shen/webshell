<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Rector\Argument\SwapFuncCallArgumentsRector;

use Iterator;
use Rector\Core\Rector\Argument\SwapFuncCallArgumentsRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class SwapFuncCallArgumentsRectorTest extends AbstractRectorTestCase
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

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            SwapFuncCallArgumentsRector::class => [
                '$newArgumentPositionsByFunctionName' => [
                    'some_function' => [1, 0],
                ],
            ],
        ];
    }
}
