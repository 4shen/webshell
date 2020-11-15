<?php

declare(strict_types=1);

namespace Rector\RemovingStatic\Printer;

use Nette\Utils\Strings;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symfony\Component\Filesystem\Filesystem;
use Symplify\SmartFileSystem\SmartFileInfo;

final class FactoryClassPrinter
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    /**
     * @var Filesystem
     */
    private $filesystem;

    public function __construct(
        NodeNameResolver $nodeNameResolver,
        BetterStandardPrinter $betterStandardPrinter,
        Filesystem $filesystem
    ) {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->filesystem = $filesystem;
    }

    public function printFactoryForClass(Class_ $factoryClass, Class_ $oldClass): void
    {
        $parentNode = $oldClass->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode instanceof Namespace_) {
            $newNamespace = clone $parentNode;
            $newNamespace->stmts = [];
            $newNamespace->stmts[] = $factoryClass;
            $nodeToPrint = $newNamespace;
        } else {
            $nodeToPrint = $factoryClass;
        }

        $factoryClassFilePath = $this->createFactoryClassFilePath($oldClass);
        $factoryClassContent = $this->betterStandardPrinter->prettyPrintFile($nodeToPrint);

        $this->filesystem->dumpFile($factoryClassFilePath, $factoryClassContent);
    }

    private function createFactoryClassFilePath(Class_ $oldClass): string
    {
        /** @var SmartFileInfo|null $classFileInfo */
        $classFileInfo = $oldClass->getAttribute(AttributeKey::FILE_INFO);
        if ($classFileInfo === null) {
            throw new ShouldNotHappenException();
        }

        $directoryPath = Strings::before($classFileInfo->getRealPath(), DIRECTORY_SEPARATOR, -1);
        $resolvedOldClass = $this->nodeNameResolver->getName($oldClass);
        if ($resolvedOldClass === null) {
            throw new ShouldNotHappenException();
        }

        $bareClassName = Strings::after($resolvedOldClass, '\\', -1) . 'Factory.php';

        return $directoryPath . DIRECTORY_SEPARATOR . $bareClassName;
    }
}
