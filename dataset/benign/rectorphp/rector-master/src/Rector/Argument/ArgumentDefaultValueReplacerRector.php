<?php

declare(strict_types=1);

namespace Rector\Core\Rector\Argument;

use Nette\Utils\Strings;
use PhpParser\BuilderHelpers;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Core\Tests\Rector\Argument\ArgumentDefaultValueReplacerRector\ArgumentDefaultValueReplacerRectorTest
 */
final class ArgumentDefaultValueReplacerRector extends AbstractRector
{
    /**
     * @var string
     */
    private const BEFORE = 'before';

    /**
     * @var string
     */
    private const AFTER = 'after';

    /**
     * @var mixed[]
     */
    private $replacesByMethodAndTypes = [];

    /**
     * @param mixed[] $replacesByMethodAndTypes
     */
    public function __construct(array $replacesByMethodAndTypes = [])
    {
        $this->replacesByMethodAndTypes = $replacesByMethodAndTypes;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Replaces defined map of arguments in defined methods and their calls.',
            [
                new ConfiguredCodeSample(
                    <<<'PHP'
$someObject = new SomeClass;
$someObject->someMethod(SomeClass::OLD_CONSTANT);
PHP
                    ,
                    <<<'PHP'
$someObject = new SomeClass;
$someObject->someMethod(false);'
PHP
                    ,
                    [
                        'SomeExampleClass' => [
                            'someMethod' => [
                                0 => [
                                    [
                                        self::BEFORE => 'SomeClass::OLD_CONSTANT',
                                        self::AFTER => 'false',
                                    ],
                                ],
                            ],
                        ],
                    ]
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
        foreach ($this->replacesByMethodAndTypes as $type => $replacesByMethods) {
            if (! $this->isMethodStaticCallOrClassMethodObjectType($node, $type)) {
                continue;
            }

            foreach ($replacesByMethods as $method => $replaces) {
                if (! $this->isName($node->name, $method)) {
                    continue;
                }

                $this->processReplaces($node, $replaces);
            }
        }

        return $node;
    }

    /**
     * @param MethodCall|StaticCall|ClassMethod $node
     * @param mixed[] $replaces
     */
    private function processReplaces(Node $node, array $replaces): Node
    {
        foreach ($replaces as $position => $oldToNewValues) {
            if ($node instanceof ClassMethod) {
                if (! isset($node->params[$position])) {
                    continue;
                }
            } elseif (isset($node->args[$position])) {
                $this->processArgs($node, $position, $oldToNewValues);
            }
        }

        return $node;
    }

    /**
     * @param MethodCall|StaticCall $node
     * @param mixed[] $oldToNewValues
     */
    private function processArgs(Node $node, int $position, array $oldToNewValues): void
    {
        $argValue = $this->getValue($node->args[$position]->value);

        foreach ($oldToNewValues as $oldToNewValue) {
            $oldValue = $oldToNewValue[self::BEFORE];
            $newValue = $oldToNewValue[self::AFTER];

            if (is_scalar($oldValue) && $argValue === $oldValue) {
                $node->args[$position] = $this->normalizeValueToArgument($newValue);
            } elseif (is_array($oldValue)) {
                $newArgs = $this->processArrayReplacement($node->args, $position, $oldValue, $newValue);

                if ($newArgs) {
                    $node->args = $newArgs;
                    break;
                }
            }
        }
    }

    private function normalizeValueToArgument($value): Arg
    {
        // class constants → turn string to composite
        if (is_string($value) && Strings::contains($value, '::')) {
            [$class, $constant] = explode('::', $value);
            $classConstantFetchNode = $this->createClassConstFetch($class, $constant);

            return new Arg($classConstantFetchNode);
        }

        return new Arg(BuilderHelpers::normalizeValue($value));
    }

    /**
     * @param Arg[] $argumentNodes
     * @param mixed[] $before
     * @param mixed|mixed[] $after
     * @return Arg[]|null
     */
    private function processArrayReplacement(array $argumentNodes, int $position, array $before, $after): ?array
    {
        $argumentValues = $this->resolveArgumentValuesToBeforeRecipe($argumentNodes, $position, $before);
        if ($argumentValues !== $before) {
            return null;
        }

        if (is_string($after)) {
            $argumentNodes[$position] = $this->normalizeValueToArgument($after);

            // clear following arguments
            $argumentCountToClear = count($before);
            for ($i = $position + 1; $i <= $position + $argumentCountToClear; ++$i) {
                unset($argumentNodes[$i]);
            }
        }

        return $argumentNodes;
    }

    /**
     * @param Arg[] $argumentNodes
     * @param mixed[] $before
     */
    private function resolveArgumentValuesToBeforeRecipe(array $argumentNodes, int $position, array $before)
    {
        $argumentValues = [];

        $beforeArgumentCount = count($before);

        for ($i = 0; $i < $beforeArgumentCount; ++$i) {
            if (! isset($argumentNodes[$position + $i])) {
                continue;
            }

            $nextArg = $argumentNodes[$position + $i];
            $argumentValues[] = $this->getValue($nextArg->value);
        }

        return $argumentValues;
    }
}
