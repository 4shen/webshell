<?php

declare(strict_types=1);

namespace Rector\Core\Tests\FileSystem\FilesFinder\ExcludePaths;

use Rector\Core\FileSystem\FilesFinder;
use Rector\Core\HttpKernel\RectorKernel;
use Symplify\PackageBuilder\Tests\AbstractKernelTestCase;

final class ExcludePathsTest extends AbstractKernelTestCase
{
    /**
     * @var FilesFinder
     */
    private $filesFinder;

    public function testShouldFail(): void
    {
        $this->bootKernelWithConfigs(RectorKernel::class, [__DIR__ . '/config/config-with-excluded-paths.yaml']);

        $this->filesFinder = self::$container->get(FilesFinder::class);
        $splFileInfos = $this->filesFinder->findInDirectoriesAndFiles([__DIR__ . '/Source'], ['php']);

        $this->assertCount(1, $splFileInfos);
    }
}
