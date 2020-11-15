<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Rector\SpecificMethod;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Empty_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use Rector\Core\Rector\AbstractPHPUnitRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\PHPUnit\Tests\Rector\SpecificMethod\AssertTrueFalseToSpecificMethodRector\AssertTrueFalseToSpecificMethodRectorTest
 */
final class AssertTrueFalseToSpecificMethodRector extends AbstractPHPUnitRector
{
    /**
     * @var string[][]|bool[][]
     */
    private const OLD_TO_NEW_METHODS = [
        'is_readable' => ['assertIsReadable', 'assertNotIsReadable'],
        'array_key_exists' => ['assertArrayHasKey', 'assertArrayNotHasKey'],
        'array_search' => ['assertContains', 'assertNotContains'],
        'in_array' => ['assertContains', 'assertNotContains'],
        'empty' => ['assertEmpty', 'assertNotEmpty'],
        'file_exists' => ['assertFileExists', 'assertFileNotExists'],
        'is_dir' => ['assertDirectoryExists', 'assertDirectoryNotExists'],
        'is_infinite' => ['assertInfinite', 'assertFinite'],
        'is_null' => ['assertNull', 'assertNotNull'],
        'is_writable' => ['assertIsWritable', 'assertNotIsWritable'],
        'is_nan' => ['assertNan', false],
        'is_a' => ['assertInstanceOf', 'assertNotInstanceOf'],
    ];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Turns true/false comparisons to their method name alternatives in PHPUnit TestCase when possible',
            [
                new CodeSample(
                    '$this->assertTrue(is_readable($readmeFile), "message");',
                    '$this->assertIsReadable($readmeFile, "message");'
                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class, StaticCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isPHPUnitMethodNames($node, ['assertTrue', 'assertFalse', 'assertNotTrue', 'assertNotFalse'])) {
            return null;
        }

        if (! isset($node->args[0])) {
            return null;
        }

        $firstArgumentValue = $node->args[0]->value;
        if ($firstArgumentValue instanceof StaticCall) {
            return null;
        }
        if (! $this->isNames($firstArgumentValue, array_keys(self::OLD_TO_NEW_METHODS))) {
            return null;
        }

        $name = $this->getName($firstArgumentValue);
        if ($name === null) {
            return null;
        }

        $this->renameMethod($node, $name);
        $this->moveFunctionArgumentsUp($node);

        return $node;
    }

    private function renameMethod(MethodCall $methodCall, string $funcName): void
    {
        /** @var Identifier $identifierNode */
        $identifierNode = $methodCall->name;
        $oldMethodName = $identifierNode->toString();

        [$trueMethodName, $falseMethodName] = self::OLD_TO_NEW_METHODS[$funcName];

        if ($trueMethodName && in_array($oldMethodName, ['assertTrue', 'assertNotFalse'], true)) {
            $methodCall->name = new Identifier($trueMethodName);
        }

        if ($falseMethodName && in_array($oldMethodName, ['assertFalse', 'assertNotTrue'], true)) {
            $methodCall->name = new Identifier($falseMethodName);
        }
    }

    /**
     * Before:
     * - $this->assertTrue(array_key_exists('...', ['...']), 'second argument');
     *
     * After:
     * - $this->assertArrayHasKey('...', ['...'], 'second argument');
     */
    private function moveFunctionArgumentsUp(MethodCall $methodCall): void
    {
        $funcCallOrEmptyNode = $methodCall->args[0]->value;
        if ($funcCallOrEmptyNode instanceof FuncCall) {
            $funcCallOrEmptyNodeName = $this->getName($funcCallOrEmptyNode);
            if ($funcCallOrEmptyNodeName === null) {
                return;
            }

            $funcCallOrEmptyNodeArgs = $funcCallOrEmptyNode->args;
            $oldArguments = $methodCall->args;
            unset($oldArguments[0]);

            $methodCall->args = $this->buildNewArguments(
                $funcCallOrEmptyNodeName,
                $funcCallOrEmptyNodeArgs,
                $oldArguments
            );
        }

        if ($funcCallOrEmptyNode instanceof Empty_) {
            $methodCall->args[0] = new Arg($funcCallOrEmptyNode->expr);
        }
    }

    /**
     * @param mixed[] $funcCallOrEmptyNodeArgs
     * @param mixed[] $oldArguments
     * @return mixed[]
     */
    private function buildNewArguments(
        string $funcCallOrEmptyNodeName,
        array $funcCallOrEmptyNodeArgs,
        array $oldArguments
    ): array {
        if (in_array($funcCallOrEmptyNodeName, ['in_array', 'array_search'], true)
            && count($funcCallOrEmptyNodeArgs) === 3) {
            unset($funcCallOrEmptyNodeArgs[2]);

            return array_merge($funcCallOrEmptyNodeArgs, $oldArguments);
        }

        if ($funcCallOrEmptyNodeName === 'is_a') {
            [$object, $class] = $funcCallOrEmptyNodeArgs;

            return array_merge([$class, $object], $oldArguments);
        }

        return array_merge($funcCallOrEmptyNodeArgs, $oldArguments);
    }
}
