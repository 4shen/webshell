<?php

declare(strict_types=1);

namespace Rector\PostRector\Collector;

use PhpParser\Node;
use PHPStan\Type\ObjectType;
use Rector\NodeTypeResolver\FileSystem\CurrentFileInfoProvider;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPStan\Type\AliasedObjectType;
use Rector\PHPStan\Type\FullyQualifiedObjectType;
use Rector\PostRector\Contract\Collector\NodeCollectorInterface;
use Symplify\SmartFileSystem\SmartFileInfo;

final class UseNodesToAddCollector implements NodeCollectorInterface
{
    /**
     * @var FullyQualifiedObjectType[][]|AliasedObjectType[][]
     */
    private $useImportTypesInFilePath = [];

    /**
     * @var string[][]
     */
    private $removedShortUsesInFilePath = [];

    /**
     * @var FullyQualifiedObjectType[][]
     */
    private $functionUseImportTypesInFilePath = [];

    /**
     * @var CurrentFileInfoProvider
     */
    private $currentFileInfoProvider;

    public function __construct(CurrentFileInfoProvider $currentFileInfoProvider)
    {
        $this->currentFileInfoProvider = $currentFileInfoProvider;
    }

    public function isActive(): bool
    {
        return count($this->useImportTypesInFilePath) > 0 || count($this->functionUseImportTypesInFilePath) > 0;
    }

    /**
     * @param FullyQualifiedObjectType|AliasedObjectType $objectType
     */
    public function addUseImport(Node $positionNode, ObjectType $objectType): void
    {
        /** @var SmartFileInfo|null $fileInfo */
        $fileInfo = $positionNode->getAttribute(AttributeKey::FILE_INFO);
        if ($fileInfo === null) {
            // fallback for freshly created Name nodes
            $fileInfo = $this->currentFileInfoProvider->getSmartFileInfo();
            if ($fileInfo === null) {
                return;
            }
        }

        $this->useImportTypesInFilePath[$fileInfo->getRealPath()][] = $objectType;
    }

    public function addFunctionUseImport(Node $node, FullyQualifiedObjectType $fullyQualifiedObjectType): void
    {
        /** @var SmartFileInfo $fileInfo */
        $fileInfo = $node->getAttribute(AttributeKey::FILE_INFO);
        $this->functionUseImportTypesInFilePath[$fileInfo->getRealPath()][] = $fullyQualifiedObjectType;
    }

    public function removeShortUse(Node $node, string $shortUse): void
    {
        /** @var SmartFileInfo|null $fileInfo */
        $fileInfo = $node->getAttribute(AttributeKey::FILE_INFO);
        if ($fileInfo === null) {
            return;
        }

        $this->removedShortUsesInFilePath[$fileInfo->getRealPath()][] = $shortUse;
    }

    public function clear(SmartFileInfo $smartFileInfo): void
    {
        // clear applied imports, so isActive() doesn't return any false positives
        unset($this->useImportTypesInFilePath[$smartFileInfo->getRealPath()], $this->functionUseImportTypesInFilePath[$smartFileInfo->getRealPath()]);
    }

    /**
     * @return FullyQualifiedObjectType[]|AliasedObjectType[]
     */
    public function getUseImportTypesByNode(Node $node): array
    {
        $filePath = $this->getRealPathFromNode($node);

        return $this->useImportTypesInFilePath[$filePath] ?? [];
    }

    public function hasImport(Node $node, FullyQualifiedObjectType $fullyQualifiedObjectType): bool
    {
        $useImports = $this->getUseImportTypesByNode($node);

        foreach ($useImports as $useImport) {
            if ($useImport->equals($fullyQualifiedObjectType)) {
                return true;
            }
        }

        return false;
    }

    public function isShortImported(Node $node, FullyQualifiedObjectType $fullyQualifiedObjectType): bool
    {
        $filePath = $this->getRealPathFromNode($node);
        if ($filePath === null) {
            return false;
        }

        $shortName = $fullyQualifiedObjectType->getShortName();

        if ($this->isShortClassImported($filePath, $shortName)) {
            return true;
        }

        $fileFunctionUseImportTypes = $this->functionUseImportTypesInFilePath[$filePath] ?? [];
        foreach ($fileFunctionUseImportTypes as $fileFunctionUseImportType) {
            if ($fileFunctionUseImportType->getShortName() === $fullyQualifiedObjectType->getShortName()) {
                return true;
            }
        }

        return false;
    }

    public function isImportShortable(Node $node, FullyQualifiedObjectType $fullyQualifiedObjectType): bool
    {
        $filePath = $this->getRealPathFromNode($node);

        $fileUseImportTypes = $this->useImportTypesInFilePath[$filePath] ?? [];

        foreach ($fileUseImportTypes as $useImportType) {
            if ($fullyQualifiedObjectType->equals($useImportType)) {
                return true;
            }
        }

        $functionImports = $this->functionUseImportTypesInFilePath[$filePath] ?? [];
        foreach ($functionImports as $useImportType) {
            if ($fullyQualifiedObjectType->equals($useImportType)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return FullyQualifiedObjectType[]|AliasedObjectType[]
     */
    public function getObjectImportsByFileInfo(SmartFileInfo $smartFileInfo): array
    {
        return $this->useImportTypesInFilePath[$smartFileInfo->getRealPath()] ?? [];
    }

    /**
     * @return FullyQualifiedObjectType[]
     */
    public function getFunctionImportsByFileInfo(SmartFileInfo $smartFileInfo): array
    {
        return $this->functionUseImportTypesInFilePath[$smartFileInfo->getRealPath()] ?? [];
    }

    /**
     * @return string[]
     */
    public function getShortUsesByFileInfo(SmartFileInfo $smartFileInfo): array
    {
        return $this->removedShortUsesInFilePath[$smartFileInfo->getRealPath()] ?? [];
    }

    private function getRealPathFromNode(Node $node): ?string
    {
        /** @var SmartFileInfo|null $fileInfo */
        $fileInfo = $node->getAttribute(AttributeKey::FILE_INFO);
        if ($fileInfo !== null) {
            return $fileInfo->getRealPath();
        }

        $currentFileInfo = $this->currentFileInfoProvider->getSmartFileInfo();
        if ($currentFileInfo === null) {
            return null;
        }

        return $currentFileInfo->getRealPath();
    }

    private function isShortClassImported(string $filePath, string $shortName): bool
    {
        $fileUseImports = $this->useImportTypesInFilePath[$filePath] ?? [];

        foreach ($fileUseImports as $fileUseImport) {
            if ($fileUseImport->getShortName() === $shortName) {
                return true;
            }
        }

        return false;
    }
}
