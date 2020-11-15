<?php

declare(strict_types=1);

namespace Rector\NetteKdyby\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\CodingStyle\Naming\ClassNaming;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\NetteKdyby\ContributeEventClassResolver;
use Rector\NetteKdyby\ValueObject\EventAndListenerTree;
use Symfony\Contracts\EventDispatcher\Event;

final class ListeningClassMethodArgumentManipulator
{
    /**
     * @var string
     */
    private const EVENT_PARAMETER_REPLACED = 'event_parameter_replaced';

    /**
     * @var ClassNaming
     */
    private $classNaming;

    /**
     * @var ContributeEventClassResolver
     */
    private $contributeEventClassResolver;

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    public function __construct(
        BetterNodeFinder $betterNodeFinder,
        ClassNaming $classNaming,
        ContributeEventClassResolver $contributeEventClassResolver,
        BetterStandardPrinter $betterStandardPrinter
    ) {
        $this->classNaming = $classNaming;
        $this->contributeEventClassResolver = $contributeEventClassResolver;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->betterStandardPrinter = $betterStandardPrinter;
    }

    public function changeFromEventAndListenerTreeAndCurrentClassName(
        EventAndListenerTree $eventAndListenerTree,
        string $className
    ): void {
        $listenerClassMethods = $eventAndListenerTree->getListenerClassMethodsByClass($className);
        if ($listenerClassMethods === []) {
            return;
        }

        $this->change($listenerClassMethods, $eventAndListenerTree);
    }

    /**
     * @param array<string, ClassMethod> $classMethodsByEventClass
     */
    public function change(array $classMethodsByEventClass, ?EventAndListenerTree $eventAndListenerTree = null): void
    {
        foreach ($classMethodsByEventClass as $eventClass => $classMethod) {
            // are attributes already replaced
            if ($classMethod->getAttribute(self::EVENT_PARAMETER_REPLACED)) {
                continue;
            }

            /** @var ClassMethod $classMethod */
            $oldParams = $classMethod->params;

            $eventClass = $eventAndListenerTree !== null ? $eventAndListenerTree->getEventClassName() : $eventClass;

            $this->changeClassParamToEventClass($eventClass, $classMethod);

            // move params to getter on event
            foreach ($oldParams as $oldParam) {
                if (! $this->isParamUsedInClassMethodBody($classMethod, $oldParam)) {
                    continue;
                }

                $eventGetterToVariableAssign = $this->createEventGetterToVariableMethodCall(
                    $eventClass,
                    $oldParam,
                    $eventAndListenerTree
                );

                $expression = new Expression($eventGetterToVariableAssign);

                $classMethod->stmts = array_merge([$expression], (array) $classMethod->stmts);
            }

            $classMethod->setAttribute(self::EVENT_PARAMETER_REPLACED, true);
        }
    }

    private function isParamUsedInClassMethodBody(ClassMethod $classMethod, Param $param): bool
    {
        return (bool) $this->betterNodeFinder->findFirst((array) $classMethod->stmts, function (Node $node) use (
            $param
        ) {
            if (! $node instanceof Variable) {
                return false;
            }

            return $this->betterStandardPrinter->areNodesEqual($node, $param->var);
        });
    }

    private function changeClassParamToEventClass(string $eventClass, ClassMethod $classMethod): void
    {
        $paramName = $this->classNaming->getVariableName($eventClass);
        $eventVariable = new Variable($paramName);

        $param = new Param($eventVariable, null, new FullyQualified($eventClass));
        $classMethod->params = [$param];
    }

    private function createEventGetterToVariableMethodCall(
        string $eventClass,
        Param $param,
        ?EventAndListenerTree $eventAndListenerTree = null
    ): Assign {
        $paramName = $this->classNaming->getVariableName($eventClass);
        $eventVariable = new Variable($paramName);

        $getterMethod = $this->contributeEventClassResolver->resolveGetterMethodByEventClassAndParam(
            $eventClass,
            $param,
            $eventAndListenerTree
        );

        $methodCall = new MethodCall($eventVariable, $getterMethod);

        return new Assign($param->var, $methodCall);
    }
}
