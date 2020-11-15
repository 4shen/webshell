<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Rector\FileSystem;

use Nette\Utils\FileSystem;
use Nette\Utils\Strings;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\FileSystemRector\Rector\AbstractFileSystemRector;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @see https://gnugat.github.io/2015/09/23/phpunit-with-phpspec.html
 *
 * @see \Rector\PhpSpecToPHPUnit\Tests\Rector\Class_\PhpSpecToPHPUnitRector\PhpSpecToPHPUnitRectorTest
 */
final class RenameSpecFileToTestFileRector extends AbstractFileSystemRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Rename "*Spec.php" file to "*Test.php" file',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
// tests/SomeSpec.php
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
// tests/SomeTest.php
CODE_SAMPLE
                ),
            ]);
    }

    public function refactor(SmartFileInfo $smartFileInfo): void
    {
        $oldRealPath = $smartFileInfo->getRealPath();

        // ends with Spec.php
        if (! Strings::endsWith($oldRealPath, 'Spec.php')) {
            return;
        }

        $newRealPath = $this->createNewRealPath($oldRealPath);

        // rename
        FileSystem::rename($oldRealPath, $newRealPath);

        // remove old file
        $this->removeFile($smartFileInfo);
    }

    private function createNewRealPath(string $oldRealPath): string
    {
        // suffix
        $newRealPath = Strings::replace($oldRealPath, '#Spec\.php$#', 'Test.php');

        // directory
        return Strings::replace($newRealPath, '#\/spec\/#', '/tests/');
    }
}
