<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\PHPStan\Scope;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PHPStan\AnalysedCodeException;
use PHPStan\Analyser\MutatingScope;
use PHPStan\Analyser\NodeScopeResolver;
use PHPStan\Node\UnreachableStatementNode;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Caching\ChangedFilesDetector;
use Rector\Caching\FileSystem\DependencyResolver;
use Rector\Core\Configuration\Configuration;
use Rector\Core\Configuration\Option;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PHPStan\Collector\TraitNodeScopeCollector;
use Rector\NodeTypeResolver\PHPStan\Scope\NodeVisitor\RemoveDeepChainMethodCallNodeVisitor;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Parameter\ParameterProvider;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @inspired by https://github.com/silverstripe/silverstripe-upgrader/blob/532182b23e854d02e0b27e68ebc394f436de0682/src/UpgradeRule/PHP/Visitor/PHPStanScopeVisitor.php
 * - https://github.com/silverstripe/silverstripe-upgrader/pull/57/commits/e5c7cfa166ad940d9d4ff69537d9f7608e992359#diff-5e0807bb3dc03d6a8d8b6ad049abd774
 */
final class PHPStanNodeScopeResolver
{
    /**
     * @var string[]
     */
    private $dependentFiles = [];

    /**
     * @var NodeScopeResolver
     */
    private $nodeScopeResolver;

    /**
     * @var ScopeFactory
     */
    private $scopeFactory;

    /**
     * @var ReflectionProvider
     */
    private $reflectionProvider;

    /**
     * @var RemoveDeepChainMethodCallNodeVisitor
     */
    private $removeDeepChainMethodCallNodeVisitor;

    /**
     * @var TraitNodeScopeCollector
     */
    private $traitNodeScopeCollector;

    /**
     * @var DependencyResolver
     */
    private $dependencyResolver;

    /**
     * @var ChangedFilesDetector
     */
    private $changedFilesDetector;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var ParameterProvider
     */
    private $parameterProvider;

    public function __construct(
        ChangedFilesDetector $changedFilesDetector,
        ScopeFactory $scopeFactory,
        NodeScopeResolver $nodeScopeResolver,
        ReflectionProvider $reflectionProvider,
        RemoveDeepChainMethodCallNodeVisitor $removeDeepChainMethodCallNodeVisitor,
        TraitNodeScopeCollector $traitNodeScopeCollector,
        DependencyResolver $dependencyResolver,
        Configuration $configuration,
        SymfonyStyle $symfonyStyle,
        ParameterProvider $parameterProvider
    ) {
        $this->scopeFactory = $scopeFactory;
        $this->nodeScopeResolver = $nodeScopeResolver;
        $this->reflectionProvider = $reflectionProvider;
        $this->removeDeepChainMethodCallNodeVisitor = $removeDeepChainMethodCallNodeVisitor;
        $this->traitNodeScopeCollector = $traitNodeScopeCollector;
        $this->dependencyResolver = $dependencyResolver;
        $this->changedFilesDetector = $changedFilesDetector;
        $this->configuration = $configuration;
        $this->symfonyStyle = $symfonyStyle;
        $this->parameterProvider = $parameterProvider;
    }

    /**
     * @param Node[] $nodes
     * @return Node[]
     */
    public function processNodes(array $nodes, SmartFileInfo $smartFileInfo): array
    {
        $this->removeDeepChainMethodCallNodes($nodes);

        $scope = $this->scopeFactory->createFromFile($smartFileInfo);

        $this->dependentFiles = [];

        // skip chain method calls, performance issue: https://github.com/phpstan/phpstan/issues/254
        $nodeCallback = function (Node $node, MutatingScope $scope): void {
            // the class reflection is resolved AFTER entering to class node
            // so we need to get it from the first after this one
            if ($node instanceof Class_ || $node instanceof Interface_) {
                $scope = $this->resolveClassOrInterfaceScope($node, $scope);
            }

            // traversing trait inside class that is using it scope (from referenced) - the trait traversed by Rector is different (directly from parsed file)
            if ($scope->isInTrait()) {
                $traitName = $scope->getTraitReflection()->getName();
                $this->traitNodeScopeCollector->addForTraitAndNode($traitName, $node, $scope);

                return;
            }

            // special case for unreachable nodes
            if ($node instanceof UnreachableStatementNode) {
                $originalNode = $node->getOriginalStatement();
                $originalNode->setAttribute(AttributeKey::IS_UNREACHABLE, true);
                $originalNode->setAttribute(AttributeKey::SCOPE, $scope);
            } else {
                $node->setAttribute(AttributeKey::SCOPE, $scope);
            }

            $this->resolveDependentFiles($node, $scope);
        };

        $safeTypes = (bool) $this->parameterProvider->provideParameter(Option::SAFE_TYPES);
        if ($safeTypes) {
            $this->removeCommentsFromNodes($nodes);
        }

        /** @var MutatingScope $scope */
        $this->nodeScopeResolver->processNodes($nodes, $scope, $nodeCallback);

        $this->reportCacheDebugAndSaveDependentFiles($smartFileInfo, $this->dependentFiles);

        return $nodes;
    }

    /**
     * @param Node[] $nodes
     */
    private function removeDeepChainMethodCallNodes(array $nodes): void
    {
        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor($this->removeDeepChainMethodCallNodeVisitor);
        $nodeTraverser->traverse($nodes);
    }

    /**
     * @param Class_|Interface_ $classOrInterfaceNode
     */
    private function resolveClassOrInterfaceScope(
        Node $classOrInterfaceNode,
        MutatingScope $mutatingScope
    ): MutatingScope {
        $className = $this->resolveClassName($classOrInterfaceNode);
        $classReflection = $this->reflectionProvider->getClass($className);

        return $mutatingScope->enterClass($classReflection);
    }

    private function resolveDependentFiles(Node $node, MutatingScope $mutatingScope): void
    {
        if (! $this->configuration->isCacheEnabled()) {
            return;
        }

        try {
            foreach ($this->dependencyResolver->resolveDependencies($node, $mutatingScope) as $dependentFile) {
                $this->dependentFiles[] = $dependentFile;
            }
        } catch (AnalysedCodeException $analysedCodeException) {
            // @ignoreException
        }
    }

    /**
     * @param string[] $dependentFiles
     */
    private function reportCacheDebugAndSaveDependentFiles(SmartFileInfo $smartFileInfo, array $dependentFiles): void
    {
        if (! $this->configuration->isCacheEnabled()) {
            return;
        }

        $this->reportCacheDebug($smartFileInfo, $dependentFiles);

        // save for cache
        $this->changedFilesDetector->addFileWithDependencies($smartFileInfo, $dependentFiles);
    }

    /**
     * @param Class_|Interface_|Trait_ $classLike
     */
    private function resolveClassName(ClassLike $classLike): string
    {
        if (isset($classLike->namespacedName)) {
            return (string) $classLike->namespacedName;
        }

        if ($classLike->name === null) {
            throw new ShouldNotHappenException();
        }

        return $classLike->name->toString();
    }

    private function reportCacheDebug(SmartFileInfo $smartFileInfo, array $dependentFiles): void
    {
        if (! $this->configuration->isCacheDebug()) {
            return;
        }

        $this->symfonyStyle->note(
            sprintf('[debug] %d dependencies for %s file', count($dependentFiles), $smartFileInfo->getRealPath())
        );

        if ($dependentFiles !== []) {
            $this->symfonyStyle->listing($dependentFiles);
        }
    }

    /**
     * Remove comments, to enable scope resolving only from code, not docblocks
     *
     * @param Node[] $nodes
     */
    private function removeCommentsFromNodes(array $nodes): void
    {
        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor(new class() extends NodeVisitorAbstract {
            public function enterNode(Node $node): ?Node
            {
                $node->setAttribute('comments', null);
                return $node;
            }
        });

        $nodeTraverser->traverse($nodes);
    }
}
