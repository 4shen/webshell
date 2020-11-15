<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ParamTypeDeclarationRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class PropertyTypeParamTypeDeclarationRectorTest extends AbstractRectorTestCase
{
    /**
     * @requires PHP >= 7.4
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $file): void
    {
        $this->doTestFileInfo($file);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/FixturePropertyType');
    }

    protected function getRectorClass(): string
    {
        return ParamTypeDeclarationRector::class;
    }

    protected function getPhpVersion(): string
    {
        return PhpVersionFeature::TYPED_PROPERTIES;
    }
}
