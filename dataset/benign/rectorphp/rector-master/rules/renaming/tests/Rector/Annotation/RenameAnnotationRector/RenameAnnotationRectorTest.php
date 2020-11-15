<?php

declare(strict_types=1);

namespace Rector\Renaming\Tests\Rector\Annotation\RenameAnnotationRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Renaming\Rector\Annotation\RenameAnnotationRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RenameAnnotationRectorTest extends AbstractRectorTestCase
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
            RenameAnnotationRector::class => [
                '$classToAnnotationMap' => [
                    'PHPUnit\Framework\TestCase' => [
                        'scenario' => 'test',
                    ],
                ],
            ],
        ];
    }
}
