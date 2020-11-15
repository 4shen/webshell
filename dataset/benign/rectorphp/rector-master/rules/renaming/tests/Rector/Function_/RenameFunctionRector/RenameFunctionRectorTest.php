<?php

declare(strict_types=1);

namespace Rector\Renaming\Tests\Rector\Function_\RenameFunctionRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Renaming\Rector\Function_\RenameFunctionRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RenameFunctionRectorTest extends AbstractRectorTestCase
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
            RenameFunctionRector::class => [
                '$oldFunctionToNewFunction' => [
                    'view' => 'Laravel\Templating\render',
                    'sprintf' => 'Safe\sprintf',
                    'hebrevc' => ['nl2br', 'hebrev'],
                ],
            ],
        ];
    }
}
