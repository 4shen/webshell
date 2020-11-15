<?php

declare(strict_types=1);

namespace Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector;

use Iterator;
use Nette\Utils\Html;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector\Source\AbstractType;
use Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector\Source\FormMacros;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RenameMethodRectorTest extends AbstractRectorTestCase
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
            RenameMethodRector::class => [
                '$oldToNewMethodsByClass' => [
                    AbstractType::class => [
                        'setDefaultOptions' => 'configureOptions',
                    ],
                    Html::class => [
                        'add' => 'addHtml',
                        'addToArray' => [
                            'name' => 'addHtmlArray',
                            'array_key' => 'hi',
                        ],
                    ],
                    FormMacros::class => [
                        'renderFormBegin' => ['Nette\Bridges\FormsLatte\Runtime', 'renderFormBegin'],
                    ],
                    '*Presenter' => [
                        'run' => '__invoke',
                    ],
                    'PHPUnit\Framework\TestClass' => [
                        'setExpectedException' => 'expectedException',
                        'setExpectedExceptionRegExp' => 'expectedException',
                    ],
                ],
            ],
        ];
    }
}
