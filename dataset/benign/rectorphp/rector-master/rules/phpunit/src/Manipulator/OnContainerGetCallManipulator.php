<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Manipulator;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\Core\PhpParser\NodeTraverser\CallableNodeTraverser;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PostRector\Collector\NodesToRemoveCollector;
use Rector\SymfonyPHPUnit\Naming\ServiceNaming;
use Rector\SymfonyPHPUnit\Node\KernelTestCaseNodeAnalyzer;

final class OnContainerGetCallManipulator
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    /**
     * @var ServiceNaming
     */
    private $serviceNaming;

    /**
     * @var KernelTestCaseNodeAnalyzer
     */
    private $kernelTestCaseNodeAnalyzer;

    /**
     * @var ValueResolver
     */
    private $valueResolver;

    /**
     * @var NodesToRemoveCollector
     */
    private $nodesToRemoveCollector;

    public function __construct(
        NodeNameResolver $nodeNameResolver,
        CallableNodeTraverser $callableNodeTraverser,
        ServiceNaming $serviceNaming,
        NodesToRemoveCollector $nodesToRemoveCollector,
        KernelTestCaseNodeAnalyzer $kernelTestCaseNodeAnalyzer,
        ValueResolver $valueResolver
    ) {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->callableNodeTraverser = $callableNodeTraverser;
        $this->serviceNaming = $serviceNaming;
        $this->kernelTestCaseNodeAnalyzer = $kernelTestCaseNodeAnalyzer;
        $this->valueResolver = $valueResolver;
        $this->nodesToRemoveCollector = $nodesToRemoveCollector;
    }

    /**
     * E.g. $someService ↓
     * $this->someService
     *
     * @param string[][] $formerVariablesByMethods
     */
    public function replaceFormerVariablesWithPropertyFetch(Class_ $class, array $formerVariablesByMethods): void
    {
        $this->callableNodeTraverser->traverseNodesWithCallable($class->stmts, function (Node $node) use (
            $formerVariablesByMethods
        ): ?PropertyFetch {
            if (! $node instanceof Variable) {
                return null;
            }

            $variableName = $this->nodeNameResolver->getName($node);
            if ($variableName === null) {
                return null;
            }

            /** @var string $methodName */
            $methodName = $node->getAttribute(AttributeKey::METHOD_NAME);
            if (! isset($formerVariablesByMethods[$methodName][$variableName])) {
                return null;
            }

            $serviceType = $formerVariablesByMethods[$methodName][$variableName];
            $propertyName = $this->serviceNaming->resolvePropertyNameFromServiceType($serviceType);

            return new PropertyFetch(new Variable('this'), $propertyName);
        });
    }

    /**
     * @return string[][]
     */
    public function removeAndCollectFormerAssignedVariables(Class_ $class, bool $skipSetUpMethod = true): array
    {
        $formerVariablesByMethods = [];

        $this->callableNodeTraverser->traverseNodesWithCallable($class->stmts, function (Node $node) use (
            &$formerVariablesByMethods,
            $skipSetUpMethod
        ): ?PropertyFetch {
            if (! $node instanceof MethodCall) {
                return null;
            }

            if ($skipSetUpMethod && $this->kernelTestCaseNodeAnalyzer->isSetUpOrEmptyMethod($node)) {
                return null;
            }

            if (! $this->kernelTestCaseNodeAnalyzer->isOnContainerGetMethodCall($node)) {
                return null;
            }

            $type = $this->valueResolver->getValue($node->args[0]->value);
            if ($type === null) {
                return null;
            }

            $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);

            if ($parentNode instanceof Assign) {
                $this->processAssign($node, $parentNode, $type, $formerVariablesByMethods);
                return null;
            }

            $propertyName = $this->serviceNaming->resolvePropertyNameFromServiceType($type);

            return new PropertyFetch(new Variable('this'), $propertyName);
        });

        return $formerVariablesByMethods;
    }

    /**
     * @param string[][] $formerVariablesByMethods
     */
    private function processAssign(
        MethodCall $methodCall,
        Assign $assign,
        string $type,
        array &$formerVariablesByMethods
    ): void {
        $variableName = $this->nodeNameResolver->getName($assign->var);
        if ($variableName === null) {
            return;
        }

        /** @var string $methodName */
        $methodName = $methodCall->getAttribute(AttributeKey::METHOD_NAME);
        $formerVariablesByMethods[$methodName][$variableName] = $type;

        $this->nodesToRemoveCollector->addNodeToRemove($assign);
    }
}
