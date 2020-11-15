<?php

declare(strict_types=1);

namespace Rector\Caching;

use Nette\Utils\Strings;
use Rector\Caching\Config\FileHashComputer;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * Inspired by https://github.com/symplify/symplify/pull/90/files#diff-72041b2e1029a08930e13d79d298ef11
 */
final class ChangedFilesDetector
{
    /**
     * @var string
     */
    private const CONFIGURATION_HASH_KEY = 'configuration_hash';

    /**
     * @var TagAwareAdapterInterface
     */
    private $tagAwareAdapter;

    /**
     * @var FileHashComputer
     */
    private $fileHashComputer;

    public function __construct(TagAwareAdapterInterface $tagAwareAdapter, FileHashComputer $fileHashComputer)
    {
        $this->tagAwareAdapter = $tagAwareAdapter;
        $this->fileHashComputer = $fileHashComputer;
    }

    /**
     * @param string[] $dependentFiles
     */
    public function addFileWithDependencies(SmartFileInfo $smartFileInfo, array $dependentFiles): void
    {
        $fileInfoCacheKey = $this->getFileInfoCacheKey($smartFileInfo);
        $hash = $this->hashFile($smartFileInfo);

        $this->saveItemWithValue($fileInfoCacheKey, $hash);

        $this->saveItemWithValue($fileInfoCacheKey . '_files', $dependentFiles);
    }

    public function hasFileChanged(SmartFileInfo $smartFileInfo): bool
    {
        $currentFileHash = $this->hashFile($smartFileInfo);

        $fileInfoCacheKey = $this->getFileInfoCacheKey($smartFileInfo);
        $item = $this->tagAwareAdapter->getItem($fileInfoCacheKey);

        $oldFileHash = $item->get();

        return $currentFileHash !== $oldFileHash;
    }

    public function invalidateFile(SmartFileInfo $smartFileInfo): void
    {
        $fileInfoCacheKey = $this->getFileInfoCacheKey($smartFileInfo);

        $this->tagAwareAdapter->deleteItem($fileInfoCacheKey);
    }

    public function clear(): void
    {
        $this->tagAwareAdapter->clear();
    }

    /**
     * @return SmartFileInfo[]
     */
    public function getDependentFileInfos(SmartFileInfo $fileInfo): array
    {
        $fileInfoCacheKey = $this->getFileInfoCacheKey($fileInfo);

        $item = $this->tagAwareAdapter->getItem($fileInfoCacheKey . '_files');
        if ($item->get() === null) {
            return [];
        }

        $dependentFileInfos = [];

        $dependentFiles = $item->get();
        foreach ($dependentFiles as $dependentFile) {
            if (! file_exists($dependentFile)) {
                continue;
            }

            $dependentFileInfos[] = new SmartFileInfo($dependentFile);
        }

        return $dependentFileInfos;
    }

    /**
     * @api
     */
    public function setFirstUsedConfig(string $firstConfig): void
    {
        // the first config is core to all → if it was changed, just invalidate it
        $configHash = $this->fileHashComputer->compute($firstConfig);
        $this->storeConfigurationDataHash($firstConfig, $configHash);
    }

    private function getFileInfoCacheKey(SmartFileInfo $smartFileInfo): string
    {
        return sha1($smartFileInfo->getRealPath());
    }

    private function hashFile(SmartFileInfo $smartFileInfo): string
    {
        return hash_file('sha1', $smartFileInfo->getRealPath());
    }

    private function saveItemWithValue(string $key, $value): void
    {
        $item = $this->tagAwareAdapter->getItem($key);
        $item->set($value);

        $this->tagAwareAdapter->save($item);
    }

    private function storeConfigurationDataHash(string $configPath, string $configurationHash): void
    {
        $key = self::CONFIGURATION_HASH_KEY . '_' . Strings::webalize($configPath);

        $this->invalidateCacheIfConfigurationChanged($key, $configurationHash);

        $this->saveItemWithValue($key, $configurationHash);
    }

    private function invalidateCacheIfConfigurationChanged(string $key, string $configurationHash): void
    {
        $cacheItem = $this->tagAwareAdapter->getItem($key);

        $oldConfigurationHash = $cacheItem->get();
        if ($configurationHash !== $oldConfigurationHash) {
            // should be unique per getcwd()
            $this->clear();
        }
    }
}
