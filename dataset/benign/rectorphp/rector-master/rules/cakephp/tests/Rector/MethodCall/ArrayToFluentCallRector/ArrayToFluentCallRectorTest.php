<?php

declare(strict_types=1);

namespace Rector\CakePHP\Tests\Rector\MethodCall\ArrayToFluentCallRector;

use Iterator;
use Rector\CakePHP\Rector\MethodCall\ArrayToFluentCallRector;
use Rector\CakePHP\Tests\Rector\MethodCall\ArrayToFluentCallRector\Source\ConfigurableClass;
use Rector\CakePHP\Tests\Rector\MethodCall\ArrayToFluentCallRector\Source\FactoryClass;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ArrayToFluentCallRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(SmartFileInfo $file): void
    {
        $this->doTestFileInfo($file);
    }

    public function provideDataForTest(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            ArrayToFluentCallRector::class => [
                '$configurableClasses' => [
                    ConfigurableClass::class => [
                        'name' => 'setName',
                        'size' => 'setSize',
                    ],
                ],
                '$factoryMethods' => [
                    FactoryClass::class => [
                        'buildClass' => [
                            'argumentPosition' => 2,
                            'class' => ConfigurableClass::class,
                        ],
                    ],
                ],
            ],
        ];
    }
}
