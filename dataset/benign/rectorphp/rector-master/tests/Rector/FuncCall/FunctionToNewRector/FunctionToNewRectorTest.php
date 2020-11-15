<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Rector\FuncCall\FunctionToNewRector;

use Iterator;
use Rector\Core\Rector\FuncCall\FunctionToNewRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class FunctionToNewRectorTest extends AbstractRectorTestCase
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
            FunctionToNewRector::class => [
                '$functionToNew' => [
                    'collection' => ['Collection'],
                ],
            ],
        ];
    }
}
