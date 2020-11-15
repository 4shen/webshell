<?php

declare(strict_types=1);

namespace Rector\NetteKdyby\Tests\Rector\MethodCall\ReplaceEventManagerWithEventSubscriberRector;

use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\NetteKdyby\Rector\MethodCall\ReplaceEventManagerWithEventSubscriberRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ReplaceEventManagerWithEventSubscriberRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $fixtureFileInfo = new SmartFileInfo(__DIR__ . '/Fixture/fixture.php.inc');
        $this->doTestFileInfo($fixtureFileInfo);

        $expectedEventFilePath = $this->originalTempFileInfo->getPath() . '/Event/SomeClassCopyEvent.php';

        $this->assertFileExists($expectedEventFilePath);
        $this->assertFileEquals(__DIR__ . '/Source/ExpectedSomeClassCopyEvent.php', $expectedEventFilePath);
    }

    protected function getRectorClass(): string
    {
        return ReplaceEventManagerWithEventSubscriberRector::class;
    }
}
