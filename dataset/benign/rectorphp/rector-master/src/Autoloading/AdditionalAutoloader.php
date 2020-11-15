<?php

declare(strict_types=1);

namespace Rector\Core\Autoloading;

use Nette\Loaders\RobotLoader;
use Rector\Core\Configuration\Option;
use Rector\Core\FileSystem\FileGuard;
use Symfony\Component\Console\Input\InputInterface;
use Symplify\SmartFileSystem\FileSystemFilter;

/**
 * Should it pass autoload files/directories to PHPStan analyzer?
 */
final class AdditionalAutoloader
{
    /**
     * @var string[]
     */
    private $autoloadPaths = [];

    /**
     * @var string[]
     */
    private $excludePaths = [];

    /**
     * @var FileGuard
     */
    private $fileGuard;

    /**
     * @var FileSystemFilter
     */
    private $fileSystemFilter;

    /**
     * @param string[] $autoloadPaths
     * @param string[] $excludePaths
     */
    public function __construct(
        FileGuard $fileGuard,
        FileSystemFilter $fileSystemFilter,
        array $autoloadPaths,
        array $excludePaths
    ) {
        $this->fileGuard = $fileGuard;
        $this->autoloadPaths = $autoloadPaths;
        $this->excludePaths = $excludePaths;
        $this->fileSystemFilter = $fileSystemFilter;
    }

    /**
     * @param string[] $source
     */
    public function autoloadWithInputAndSource(InputInterface $input, array $source): void
    {
        $autoloadDirectories = $this->fileSystemFilter->filterDirectories($this->autoloadPaths);
        $autoloadFiles = $this->fileSystemFilter->filterFiles($this->autoloadPaths);

        $this->autoloadFileFromInput($input);
        $this->autoloadDirectories($autoloadDirectories);
        $this->autoloadFiles($autoloadFiles);

        // the scanned file needs to be autoloaded
        $directories = $this->fileSystemFilter->filterDirectories($source);
        foreach ($directories as $directory) {
            // load project autoload
            if (file_exists($directory . '/vendor/autoload.php')) {
                require_once $directory . '/vendor/autoload.php';
            }
        }
    }

    private function autoloadFileFromInput(InputInterface $input): void
    {
        if (! $input->hasOption(Option::OPTION_AUTOLOAD_FILE)) {
            return;
        }

        /** @var string|null $autoloadFile */
        $autoloadFile = $input->getOption(Option::OPTION_AUTOLOAD_FILE);
        if ($autoloadFile === null) {
            return;
        }

        $this->autoloadFiles([$autoloadFile]);
    }

    /**
     * @param string[] $directories
     */
    private function autoloadDirectories(array $directories): void
    {
        if (count($directories) === 0) {
            return;
        }

        $robotLoader = new RobotLoader();
        $robotLoader->ignoreDirs[] = '*Fixtures';
        foreach ($this->excludePaths as $excludePath) {
            $robotLoader->ignoreDirs[] = $excludePath;
        }
        // last argument is workaround: https://github.com/nette/robot-loader/issues/12
        $robotLoader->setTempDirectory(sys_get_temp_dir() . '/_rector_robot_loader');

        foreach ($directories as $autoloadDirectory) {
            $robotLoader->addDirectory($autoloadDirectory);
        }

        $robotLoader->register();
    }

    /**
     * @param string[] $files
     */
    private function autoloadFiles(array $files): void
    {
        foreach ($files as $file) {
            $this->fileGuard->ensureFileExists($file, 'Extra autoload');

            require_once $file;
        }
    }
}
