<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Rector\Argument\ArgumentAdderRector;

use Iterator;
use Rector\Core\Rector\Argument\ArgumentAdderRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Core\Tests\Rector\Argument\ArgumentAdderRector\Source\SomeContainerBuilder;
use Rector\Core\Tests\Rector\Argument\ArgumentAdderRector\Source\SomeParentClient;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ArgumentAdderRectorTest extends AbstractRectorTestCase
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
            ArgumentAdderRector::class => [
                '$positionWithDefaultValueByMethodNamesByClassTypes' => [
                    SomeContainerBuilder::class => [
                        'compile' => [
                            0 => [
                                'name' => 'isCompiled',
                                'default_value' => false,
                            ],
                        ],
                        'addCompilerPass' => [
                            2 => [
                                'name' => 'priority',
                                'default_value' => 0,
                                'type' => 'int',
                            ],
                        ],
                    ],

                    // scoped
                    SomeParentClient::class => [
                        'submit' => [
                            2 => [
                                'name' => 'serverParameters',
                                'default_value' => [],
                                'type' => 'array',
                                // scope!
                                'scope' => ['parent_call', 'class_method'],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
