<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Name;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\CodeQuality\Tests\Rector\FuncCall\ArrayKeysAndInArrayToArrayKeyExistsRector\ArrayKeysAndInArrayToArrayKeyExistsRectorTest
 */
final class ArrayKeysAndInArrayToArrayKeyExistsRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replace array_keys() and in_array() to array_key_exists()', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run($packageName, $values)
    {
        $keys = array_keys($values);
        return in_array($packageName, $keys, true);
    }
}
PHP
,
                <<<'PHP'
class SomeClass
{
    public function run($packageName, $values)
    {
        return array_key_exists($packageName, $values);
    }
}
PHP

            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }

    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isFuncCallName($node, 'in_array')) {
            return null;
        }

        $arrayVariable = $node->args[1]->value;

        /** @var Assign|Node|null $previousAssignArraysKeysFuncCall */
        $previousAssignArraysKeysFuncCall = $this->betterNodeFinder->findFirstPrevious($node, function (Node $node) use (
            $arrayVariable
        ) {
            // breaking out of scope
            if ($node instanceof FunctionLike) {
                return true;
            }

            if (! $node instanceof Assign) {
                return false;
            }

            if (! $this->areNodesEqual($arrayVariable, $node->var)) {
                return false;
            }

            return $this->isFuncCallName($node->expr, 'array_keys');
        });

        if (! $previousAssignArraysKeysFuncCall instanceof Assign) {
            return null;
        }

        /** @var FuncCall $arrayKeysFuncCall */
        $arrayKeysFuncCall = $previousAssignArraysKeysFuncCall->expr;

        $this->removeNode($previousAssignArraysKeysFuncCall);

        return $this->createArrayKeyExists($node, $arrayKeysFuncCall);
    }

    private function createArrayKeyExists(FuncCall $inArrayFuncCall, FuncCall $arrayKeysFuncCall): FuncCall
    {
        $arguments = [$inArrayFuncCall->args[0], $arrayKeysFuncCall->args[0]];

        return new FuncCall(new Name('array_key_exists'), $arguments);
    }
}
