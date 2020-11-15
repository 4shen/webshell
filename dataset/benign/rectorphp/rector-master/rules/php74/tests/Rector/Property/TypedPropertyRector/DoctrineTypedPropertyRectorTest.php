<?php

declare(strict_types=1);

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Php74\Rector\Property\TypedPropertyRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class DoctrineTypedPropertyRectorTest extends AbstractRectorTestCase
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
        return $this->yieldFilesFromDirectory(__DIR__ . '/FixtureDoctrine');
    }

    protected function getRectorsWithConfiguration(): array
    {
        return [
            TypedPropertyRector::class => [
                '$classLikeTypeOnly' => false,
            ],
        ];
    }

    protected function getPhpVersion(): string
    {
        return PhpVersionFeature::BEFORE_UNION_TYPES;
    }
}
