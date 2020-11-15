<?php

declare(strict_types=1);

namespace Rector\Core\Rector\Argument;

use PhpParser\BuilderHelpers;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\Core\Tests\Rector\Argument\ArgumentAdderRector\ArgumentAdderRectorTest
 */
final class ArgumentAdderRector extends AbstractRector
{
    /**
     * @var string[][][][][]
     */
    private const CONFIGURATION = [
        '$positionWithDefaultValueByMethodNamesByClassTypes' => [
            'SomeExampleClass' => [
                'someMethod' => [
                    0 => [
                        'name' => 'someArgument',
                        'default_value' => 'true',
                        'type' => 'SomeType',
                    ],
                ],
            ],
        ],
    ];

    /**
     * @var mixed[]
     */
    private $positionWithDefaultValueByMethodNamesByClassTypes = [];

    /**
     * @param mixed[] $positionWithDefaultValueByMethodNamesByClassTypes
     */
    public function __construct(array $positionWithDefaultValueByMethodNamesByClassTypes = [])
    {
        $this->positionWithDefaultValueByMethodNamesByClassTypes = $positionWithDefaultValueByMethodNamesByClassTypes;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'This Rector adds new default arguments in calls of defined methods and class types.',
            [
                new ConfiguredCodeSample(
                    <<<'PHP'
$someObject = new SomeExampleClass;
$someObject->someMethod();
PHP
                    ,
                    <<<'PHP'
$someObject = new SomeExampleClass;
$someObject->someMethod(true);
PHP
                    ,
                    self::CONFIGURATION
                ),
                new ConfiguredCodeSample(
                    <<<'PHP'
class MyCustomClass extends SomeExampleClass
{
    public function someMethod()
    {
    }
}
PHP
                    ,
                    <<<'PHP'
class MyCustomClass extends SomeExampleClass
{
    public function someMethod($value = true)
    {
    }
}
PHP
                    ,
                    self::CONFIGURATION
                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class, StaticCall::class, ClassMethod::class];
    }

    /**
     * @param MethodCall|StaticCall|ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($this->positionWithDefaultValueByMethodNamesByClassTypes as $type => $positionWithDefaultValueByMethodNames) {
            if (! $this->isObjectTypeMatch($node, $type)) {
                continue;
            }

            foreach ($positionWithDefaultValueByMethodNames as $method => $positionWithDefaultValues) {
                if (! $this->isName($node->name, $method)) {
                    continue;
                }

                $this->processPositionWithDefaultValues($node, $positionWithDefaultValues);
            }
        }

        return $node;
    }

    /**
     * @param MethodCall|StaticCall|ClassMethod $node
     */
    private function isObjectTypeMatch(Node $node, string $type): bool
    {
        if ($node instanceof MethodCall) {
            return $this->isObjectType($node->var, $type);
        }

        if ($node instanceof StaticCall) {
            return $this->isObjectType($node->class, $type);
        }

        // ClassMethod
        /** @var Class_|null $class */
        $class = $node->getAttribute(AttributeKey::CLASS_NODE);

        // anonymous class
        if ($class === null) {
            return false;
        }

        return $this->isObjectType($class, $type);
    }

    /**
     * @param ClassMethod|MethodCall|StaticCall $node
     * @param mixed[] $positionWithDefaultValues
     */
    private function processPositionWithDefaultValues(Node $node, array $positionWithDefaultValues): void
    {
        foreach ($positionWithDefaultValues as $position => $parameterConfiguration) {
            $name = $parameterConfiguration['name'];
            $defaultValue = $parameterConfiguration['default_value'] ?? null;
            $type = $parameterConfiguration['type'] ?? null;

            if ($this->shouldSkipParameter($node, $position, $name, $parameterConfiguration)) {
                continue;
            }

            if ($node instanceof ClassMethod) {
                $this->addClassMethodParam($node, $name, $defaultValue, $type, $position);
            } elseif ($node instanceof StaticCall) {
                $this->processStaticCall($node, $position, $name);
            } else {
                $arg = new Arg(BuilderHelpers::normalizeValue($defaultValue));
                $node->args[$position] = $arg;
            }
        }
    }

    /**
     * @param ClassMethod|MethodCall|StaticCall $node
     * @param mixed[] $parameterConfiguration
     */
    private function shouldSkipParameter(Node $node, int $position, string $name, array $parameterConfiguration): bool
    {
        if ($node instanceof ClassMethod) {
            // already added?
            return isset($node->params[$position]) && $this->isName($node->params[$position], $name);
        }

        // already added?
        if (isset($node->args[$position]) && $this->isName($node->args[$position], $name)) {
            return true;
        }

        // is correct scope?
        return ! $this->isInCorrectScope($node, $parameterConfiguration);
    }

    private function addClassMethodParam(
        ClassMethod $classMethod,
        string $name,
        $defaultValue,
        ?string $type,
        int $position
    ): void {
        $param = new Param(new Variable($name), BuilderHelpers::normalizeValue($defaultValue));
        if ($type) {
            $param->type = ctype_upper($type[0]) ? new FullyQualified($type) : new Identifier($type);
        }

        $classMethod->params[$position] = $param;
    }

    private function processStaticCall(StaticCall $staticCall, int $position, $name): void
    {
        if (! $staticCall->class instanceof Name) {
            return;
        }

        if (! $this->isName($staticCall->class, 'parent')) {
            return;
        }

        $staticCall->args[$position] = new Arg(new Variable($name));
    }

    /**
     * @param ClassMethod|MethodCall|StaticCall $node
     * @param mixed[] $parameterConfiguration
     */
    private function isInCorrectScope(Node $node, array $parameterConfiguration): bool
    {
        if (! isset($parameterConfiguration['scope'])) {
            return true;
        }

        /** @var string[] $scope */
        $scope = $parameterConfiguration['scope'];

        if ($node instanceof ClassMethod) {
            return in_array('class_method', $scope, true);
        }

        if ($node instanceof StaticCall) {
            if (! $node->class instanceof Name) {
                return false;
            }

            if ($this->isName($node->class, 'parent')) {
                return in_array('parent_call', $scope, true);
            }

            return in_array('method_call', $scope, true);
        }

        // MethodCall
        return in_array('method_call', $scope, true);
    }
}
