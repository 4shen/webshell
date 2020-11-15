<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class Php80RectorTest extends AbstractRectorTestCase
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
        return $this->yieldFilesFromDirectory(__DIR__ . '/FixtureForPhp80');
    }

    protected function getPhpVersion(): string
    {
        return PhpVersionFeature::STATIC_RETURN_TYPE;
    }

    protected function getRectorClass(): string
    {
        return ReturnTypeDeclarationRector::class;
    }
}
