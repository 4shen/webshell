<?php

declare(strict_types=1);

namespace Rector\NetteTesterToPHPUnit\Tests\Rector\RenameTesterTestToPHPUnitToTestFileRector;

use Rector\Core\Testing\PHPUnit\AbstractFileSystemRectorTestCase;
use Rector\NetteTesterToPHPUnit\Rector\RenameTesterTestToPHPUnitToTestFileRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RenameTesterTestToPHPUnitToTestFileRectorTest extends AbstractFileSystemRectorTestCase
{
    public function test(): void
    {
        $fixtureFileInfo = new SmartFileInfo(__DIR__ . '/Source/SomeCase.phpt');
        $this->doTestFileInfo($fixtureFileInfo);

        $temporaryFilePath = $this->getFixtureTempDirectory() . '/Source/SomeCase.phpt';

        // PHPUnit 9.0 ready
        if (method_exists($this, 'assertFileDoesNotExist')) {
            $this->assertFileDoesNotExist($temporaryFilePath);
        } else {
            // PHPUnit 8.0 ready
            $this->assertFileNotExists($temporaryFilePath);
        }

        $this->assertFileExists($this->getFixtureTempDirectory() . '/Source/SomeCaseTest.php');
    }

    protected function getRectorClass(): string
    {
        return RenameTesterTestToPHPUnitToTestFileRector::class;
    }
}
