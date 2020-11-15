<?php

declare(strict_types=1);

namespace Rector\Core\Console\Command;

use Rector\Caching\ChangedFilesDetector;
use Rector\Caching\UnchangedFilesFilter;
use Rector\ChangesReporting\Application\ErrorAndDiffCollector;
use Rector\ChangesReporting\Output\ConsoleOutputFormatter;
use Rector\Core\Application\RectorApplication;
use Rector\Core\Autoloading\AdditionalAutoloader;
use Rector\Core\Configuration\Configuration;
use Rector\Core\Configuration\Option;
use Rector\Core\Console\Output\OutputFormatterCollector;
use Rector\Core\EventDispatcher\Event\AfterReportEvent;
use Rector\Core\FileSystem\FilesFinder;
use Rector\Core\Guard\RectorGuard;
use Rector\Core\NonPhpFile\NonPhpFileProcessor;
use Rector\Core\PhpParser\NodeTraverser\RectorNodeTraverser;
use Rector\Core\Stubs\StubLoader;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symplify\PackageBuilder\Console\Command\CommandNaming;
use Symplify\PackageBuilder\Console\ShellCode;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ProcessCommand extends AbstractCommand
{
    /**
     * @var FilesFinder
     */
    private $filesFinder;

    /**
     * @var AdditionalAutoloader
     */
    private $additionalAutoloader;

    /**
     * @var RectorGuard
     */
    private $rectorGuard;

    /**
     * @var ErrorAndDiffCollector
     */
    private $errorAndDiffCollector;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var RectorApplication
     */
    private $rectorApplication;

    /**
     * @var OutputFormatterCollector
     */
    private $outputFormatterCollector;

    /**
     * @var RectorNodeTraverser
     */
    private $rectorNodeTraverser;

    /**
     * @var StubLoader
     */
    private $stubLoader;

    /**
     * @var NonPhpFileProcessor
     */
    private $nonPhpFileProcessor;

    /**
     * @var UnchangedFilesFilter
     */
    private $unchangedFilesFilter;

    /**
     * @var ChangedFilesDetector
     */
    private $changedFilesDetector;

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(
        FilesFinder $phpFilesFinder,
        AdditionalAutoloader $additionalAutoloader,
        RectorGuard $rectorGuard,
        ErrorAndDiffCollector $errorAndDiffCollector,
        Configuration $configuration,
        RectorApplication $rectorApplication,
        OutputFormatterCollector $outputFormatterCollector,
        RectorNodeTraverser $rectorNodeTraverser,
        StubLoader $stubLoader,
        NonPhpFileProcessor $nonPhpFileProcessor,
        ChangedFilesDetector $changedFilesDetector,
        UnchangedFilesFilter $unchangedFilesFilter,
        SymfonyStyle $symfonyStyle,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->filesFinder = $phpFilesFinder;
        $this->additionalAutoloader = $additionalAutoloader;
        $this->rectorGuard = $rectorGuard;
        $this->errorAndDiffCollector = $errorAndDiffCollector;
        $this->configuration = $configuration;
        $this->rectorApplication = $rectorApplication;
        $this->outputFormatterCollector = $outputFormatterCollector;
        $this->rectorNodeTraverser = $rectorNodeTraverser;
        $this->stubLoader = $stubLoader;
        $this->nonPhpFileProcessor = $nonPhpFileProcessor;
        $this->unchangedFilesFilter = $unchangedFilesFilter;

        parent::__construct();

        $this->changedFilesDetector = $changedFilesDetector;
        $this->symfonyStyle = $symfonyStyle;
        $this->eventDispatcher = $eventDispatcher;
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setAliases(['rectify']);

        $this->setDescription('Upgrade or refactor source code with provided rectors');
        $this->addArgument(
            Option::SOURCE,
            InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
            'Files or directories to be upgraded.'
        );
        $this->addOption(
            Option::OPTION_DRY_RUN,
            'n',
            InputOption::VALUE_NONE,
            'See diff of changes, do not save them to files.'
        );

        $this->addOption(
            Option::OPTION_AUTOLOAD_FILE,
            'a',
            InputOption::VALUE_REQUIRED,
            'File with extra autoload'
        );

        $this->addOption(
            Option::MATCH_GIT_DIFF,
            null,
            InputOption::VALUE_NONE,
            'Execute only on file(s) matching the git diff.'
        );

        $this->addOption(
            Option::OPTION_ONLY,
            null,
            InputOption::VALUE_REQUIRED,
            'Run only one single Rector from the loaded Rectors (in services, sets, etc).'
        );

        $availableOutputFormatters = $this->outputFormatterCollector->getNames();
        $this->addOption(
            Option::OPTION_OUTPUT_FORMAT,
            'o',
            InputOption::VALUE_OPTIONAL,
            sprintf('Select output format: "%s".', implode('", "', $availableOutputFormatters)),
            ConsoleOutputFormatter::NAME
        );

        $this->addOption(
            Option::OPTION_NO_PROGRESS_BAR,
            null,
            InputOption::VALUE_NONE,
            'Hide progress bar. Useful e.g. for nicer CI output.'
        );

        $this->addOption(
            Option::OPTION_OUTPUT_FILE,
            null,
            InputOption::VALUE_REQUIRED,
            'Location for file to dump result in. Useful for Docker or automated processes'
        );

        $this->addOption(Option::CACHE_DEBUG, null, InputOption::VALUE_NONE, 'Debug changed file cache');
        $this->addOption(Option::OPTION_CLEAR_CACHE, null, InputOption::VALUE_NONE, 'Clear un-chaged files cache');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->configuration->resolveFromInput($input);
        $this->configuration->setAreAnyPhpRectorsLoaded((bool) $this->rectorNodeTraverser->getPhpRectorCount());

        $this->rectorGuard->ensureSomeRectorsAreRegistered();
        $this->rectorGuard->ensureGetNodeTypesAreNodes();

        $this->stubLoader->loadStubs();

        $source = $this->configuration->getPaths();

        $phpFileInfos = $this->filesFinder->findInDirectoriesAndFiles(
            $source,
            $this->configuration->getFileExtensions(),
            $this->configuration->mustMatchGitDiff()
        );

        $this->additionalAutoloader->autoloadWithInputAndSource($input, $source);

        $phpFileInfos = $this->processWithCache($phpFileInfos);

        if ($this->configuration->isCacheDebug()) {
            $this->symfonyStyle->note(sprintf('[cache] %d files after cache filter', count($phpFileInfos)));
            $this->symfonyStyle->listing($phpFileInfos);
        }

        $this->configuration->setFileInfos($phpFileInfos);
        $this->rectorApplication->runOnFileInfos($phpFileInfos);

        // must run after PHP rectors, because they might change class names, and these class names must be changed in configs
        $neonYamlFileInfos = $this->filesFinder->findInDirectoriesAndFiles($source, ['neon', 'yaml', 'xml']);
        $this->nonPhpFileProcessor->runOnFileInfos($neonYamlFileInfos);

        $this->reportZeroCacheRectorsCondition();

        // report diffs and errors
        $outputFormat = (string) $input->getOption(Option::OPTION_OUTPUT_FORMAT);

        $outputFormatter = $this->outputFormatterCollector->getByName($outputFormat);
        $outputFormatter->report($this->errorAndDiffCollector);

        $this->eventDispatcher->dispatch(new AfterReportEvent());

        // invalidate affected files
        $this->invalidateAffectedCacheFiles();

        // some errors were found → fail
        if ($this->errorAndDiffCollector->getErrors() !== []) {
            return ShellCode::SUCCESS;
        }

        // inverse error code for CI dry-run
        if ($this->configuration->isDryRun() && $this->errorAndDiffCollector->getFileDiffsCount()) {
            return ShellCode::ERROR;
        }

        return ShellCode::SUCCESS;
    }

    /**
     * @param SmartFileInfo[] $phpFileInfos
     * @return SmartFileInfo[]
     */
    private function processWithCache(array $phpFileInfos): array
    {
        if (! $this->configuration->isCacheEnabled()) {
            return $phpFileInfos;
        }

        // cache stuff
        if ($this->configuration->shouldClearCache()) {
            $this->changedFilesDetector->clear();
        }

        if ($this->configuration->isCacheDebug()) {
            $this->symfonyStyle->note(sprintf('[cache] %d files before cache filter', count($phpFileInfos)));
        }

        return $this->unchangedFilesFilter->filterAndJoinWithDependentFileInfos($phpFileInfos);
    }

    private function reportZeroCacheRectorsCondition(): void
    {
        if (! $this->configuration->isCacheEnabled()) {
            return;
        }

        if (! $this->rectorNodeTraverser->hasZeroCacheRectors()) {
            return;
        }

        $this->symfonyStyle->note(sprintf(
            'Ruleset contains %d rules that need "--clear-cache" option to analyse full project',
            $this->rectorNodeTraverser->getZeroCacheRectorCount()
        ));
    }

    private function invalidateAffectedCacheFiles(): void
    {
        if (! $this->configuration->isCacheEnabled()) {
            return;
        }

        foreach ($this->errorAndDiffCollector->getAffectedFileInfos() as $affectedFileInfo) {
            $this->changedFilesDetector->invalidateFile($affectedFileInfo);
        }
    }
}
