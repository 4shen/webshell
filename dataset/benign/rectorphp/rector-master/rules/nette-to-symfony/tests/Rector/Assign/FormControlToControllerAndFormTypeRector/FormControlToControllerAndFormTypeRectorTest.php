<?php

declare(strict_types=1);

namespace Rector\NetteToSymfony\Tests\Rector\Assign\FormControlToControllerAndFormTypeRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\NetteToSymfony\Rector\Assign\FormControlToControllerAndFormTypeRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class FormControlToControllerAndFormTypeRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(
        SmartFileInfo $fileInfo,
        string $expectedExtraFileName,
        string $expectedExtraContentFilePath
    ): void {
        $this->doTestFileInfo($fileInfo);
        $this->doTestExtraFile($expectedExtraFileName, $expectedExtraContentFilePath);
    }

    public function provideData(): Iterator
    {
        yield [
            new SmartFileInfo(__DIR__ . '/Fixture/fixture.php.inc'),
            'SomeFormController.php',
            __DIR__ . '/Source/extra_file.php',
        ];
    }

    protected function getRectorClass(): string
    {
        return FormControlToControllerAndFormTypeRector::class;
    }
}
