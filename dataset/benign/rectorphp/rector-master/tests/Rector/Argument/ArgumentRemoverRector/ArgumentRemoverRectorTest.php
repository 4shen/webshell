<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Rector\Argument\ArgumentRemoverRector;

use Iterator;
use Rector\Core\Rector\Argument\ArgumentRemoverRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Core\Tests\Rector\Argument\ArgumentRemoverRector\Source\Persister;
use Rector\Core\Tests\Rector\Argument\ArgumentRemoverRector\Source\RemoveInTheMiddle;
use Symfony\Component\Yaml\Yaml;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ArgumentRemoverRectorTest extends AbstractRectorTestCase
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
            ArgumentRemoverRector::class =>
            [
                '$positionsByMethodNameByClassType' => [
                    Persister::class => [
                        'getSelectJoinColumnSQL' => [
                            4 => null,
                        ],
                    ],
                    Yaml::class => [
                        'parse' => [
                            1 => ['Symfony\Component\Yaml\Yaml::PARSE_KEYS_AS_STRINGS', 'hey', 55, 5.5],
                        ],
                    ],
                    RemoveInTheMiddle::class => [
                        'run' => [
                            1 => [
                                'name' => 'second',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
