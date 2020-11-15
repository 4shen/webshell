<?php

declare(strict_types=1);

namespace Rector\Core\Rector\MagicDisclosure;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\Core\PhpParser\Node\Manipulator\PropertyFetchManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\Core\Tests\Rector\MagicDisclosure\GetAndSetToMethodCallRector\GetAndSetToMethodCallRectorTest
 */
final class GetAndSetToMethodCallRector extends AbstractRector
{
    /**
     * @var string[][]
     */
    private $typeToMethodCalls = [];

    /**
     * @var PropertyFetchManipulator
     */
    private $propertyFetchManipulator;

    /**
     * Type to method call()
     *
     * @param string[][] $typeToMethodCalls
     */
    public function __construct(PropertyFetchManipulator $propertyFetchManipulator, array $typeToMethodCalls = [])
    {
        $this->propertyFetchManipulator = $propertyFetchManipulator;
        $this->typeToMethodCalls = $typeToMethodCalls;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns defined `__get`/`__set` to specific method calls.', [
            new ConfiguredCodeSample(
<<<'PHP'
$container = new SomeContainer;
$container->someService = $someService;
PHP
                ,
<<<'PHP'
$container = new SomeContainer;
$container->setService("someService", $someService);
PHP
                ,
                [
                    'SomeContainer' => [
                        'set' => 'addService',
                    ],
                ]
            ),
            new ConfiguredCodeSample(
                <<<'PHP'
$container = new SomeContainer;
$someService = $container->someService;
PHP
                ,
                <<<'PHP'
$container = new SomeContainer;
$someService = $container->getService("someService");
PHP
                ,
                [
                    '$typeToMethodCalls' => [
                        'SomeContainer' => [
                            'get' => 'getService',
                        ],
                    ],
                ]
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Assign::class, PropertyFetch::class];
    }

    /**
     * @param Assign|PropertyFetch $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Assign) {
            if ($node->var instanceof PropertyFetch || $node->var instanceof StaticPropertyFetch) {
                return $this->processMagicSet($node);
            }

            return null;
        }

        return $this->processPropertyFetch($node);
    }

    private function processMagicSet(Assign $assign): ?Node
    {
        /** @var PropertyFetch $propertyFetchNode */
        $propertyFetchNode = $assign->var;

        foreach ($this->typeToMethodCalls as $type => $transformation) {
            $objectType = new ObjectType($type);
            if ($this->shouldSkipPropertyFetch($propertyFetchNode, $objectType)) {
                continue;
            }

            return $this->createMethodCallNodeFromAssignNode(
                $propertyFetchNode,
                $assign->expr,
                $transformation['set']
            );
        }

        return null;
    }

    private function processPropertyFetch(PropertyFetch $propertyFetch): ?MethodCall
    {
        foreach ($this->typeToMethodCalls as $type => $transformation) {
            $objectType = new ObjectType($type);
            if ($this->shouldSkipPropertyFetch($propertyFetch, $objectType)) {
                continue;
            }

            // setter, skip
            $parentNode = $propertyFetch->getAttribute(AttributeKey::PARENT_NODE);

            if ($parentNode instanceof Assign && $parentNode->var === $propertyFetch) {
                continue;
            }

            return $this->createMethodCallNodeFromPropertyFetchNode($propertyFetch, $transformation['get']);
        }

        return null;
    }

    private function shouldSkipPropertyFetch(PropertyFetch $propertyFetch, ObjectType $objectType): bool
    {
        if (! $this->isObjectType($propertyFetch->var, $objectType)) {
            return true;
        }

        if (! $this->propertyFetchManipulator->isMagicOnType($propertyFetch, $objectType)) {
            return true;
        }

        // $this->value = $value
        return $this->propertyFetchManipulator->isPropertyToSelf($propertyFetch);
    }

    private function createMethodCallNodeFromAssignNode(
        PropertyFetch $propertyFetch,
        Node $node,
        string $method
    ): MethodCall {
        /** @var Variable $variableNode */
        $variableNode = $propertyFetch->var;

        return $this->createMethodCall($variableNode, $method, [$this->getName($propertyFetch), $node]);
    }

    private function createMethodCallNodeFromPropertyFetchNode(
        PropertyFetch $propertyFetch,
        string $method
    ): MethodCall {
        /** @var Variable $variableNode */
        $variableNode = $propertyFetch->var;

        return $this->createMethodCall($variableNode, $method, [$this->getName($propertyFetch)]);
    }
}
