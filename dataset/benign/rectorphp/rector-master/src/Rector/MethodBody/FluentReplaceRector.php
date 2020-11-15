<?php

declare(strict_types=1);

namespace Rector\Core\Rector\MethodBody;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\Core\Tests\Rector\MethodBody\FluentReplaceRector\FluentReplaceRectorTest
 */
final class FluentReplaceRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $classesToDefluent = [];

    /**
     * @param string[] $classesToDefluent
     */
    public function __construct(array $classesToDefluent = [])
    {
        $this->classesToDefluent = $classesToDefluent;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns fluent interface calls to classic ones.', [
            new ConfiguredCodeSample(
                <<<'PHP'
$someClass = new SomeClass();
$someClass->someFunction()
            ->otherFunction();
PHP
                ,
                <<<'PHP'
$someClass = new SomeClass();
$someClass->someFunction();
$someClass->otherFunction();
PHP
                ,
                [
                    '$classesToDefluent' => ['SomeExampleClass'],
                ]
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isLastMethodCallInChainCall($node)) {
            return null;
        }

        $chainMethodCalls = $this->collectAllMethodCallsInChain($node);
        if (! $this->areChainMethodCallsMatching($chainMethodCalls)) {
            return null;
        }

        $decoupledMethodCalls = $this->createNonFluentMethodCalls($chainMethodCalls);

        $currentOne = array_pop($decoupledMethodCalls);

        // add separated method calls
        /** @var MethodCall[] $decoupledMethodCalls */
        $decoupledMethodCalls = array_reverse($decoupledMethodCalls);
        foreach ($decoupledMethodCalls as $decoupledMethodCall) {
            // needed to remove weird spacing
            $decoupledMethodCall->setAttribute('origNode', null);

            $this->addNodeAfterNode($decoupledMethodCall, $node);
        }

        return $currentOne;
    }

    private function isLastMethodCallInChainCall(MethodCall $methodCall): bool
    {
        // is chain method call
        if (! $methodCall->var instanceof MethodCall) {
            return false;
        }

        $nextNode = $methodCall->getAttribute(AttributeKey::NEXT_NODE);

        // is last chain call
        return $nextNode === null;
    }

    /**
     * @return MethodCall[]
     */
    private function collectAllMethodCallsInChain(MethodCall $methodCall): array
    {
        $chainMethodCalls = [$methodCall];

        $currentNode = $methodCall->var;
        while ($currentNode instanceof MethodCall) {
            $chainMethodCalls[] = $currentNode;
            $currentNode = $currentNode->var;
        }

        return $chainMethodCalls;
    }

    /**
     * @param MethodCall[] $chainMethodCalls
     */
    private function areChainMethodCallsMatching(array $chainMethodCalls): bool
    {
        // is matching type all the way?
        foreach ($chainMethodCalls as $chainMethodCall) {
            if (! $this->isMatchingMethodCall($chainMethodCall)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param MethodCall[] $chainMethodCalls
     * @return MethodCall[]
     */
    private function createNonFluentMethodCalls(array $chainMethodCalls): array
    {
        $rootVariable = $this->extractRootVariable($chainMethodCalls);

        $decoupledMethodCalls = [];
        foreach ($chainMethodCalls as $chainMethodCall) {
            $chainMethodCall->var = $rootVariable;
            $decoupledMethodCalls[] = $chainMethodCall;
        }

        return $decoupledMethodCalls;
    }

    private function isMatchingMethodCall(MethodCall $methodCall): bool
    {
        foreach ($this->classesToDefluent as $classToDefluent) {
            if ($this->isObjectType($methodCall, $classToDefluent)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param MethodCall[] $methodCalls
     * @return Variable|PropertyFetch
     */
    private function extractRootVariable(array $methodCalls): Expr
    {
        foreach ($methodCalls as $methodCall) {
            if ($methodCall->var instanceof Variable) {
                return $methodCall->var;
            }

            if ($methodCall->var instanceof PropertyFetch) {
                return $methodCall->var;
            }
        }

        throw new ShouldNotHappenException();
    }
}
