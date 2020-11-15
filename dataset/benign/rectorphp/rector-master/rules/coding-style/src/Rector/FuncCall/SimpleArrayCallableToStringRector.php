<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\CodingStyle\Tests\Rector\FuncCall\SimpleArrayCallableToStringRector\SimpleArrayCallableToStringRectorTest
 */
final class SimpleArrayCallableToStringRector extends AbstractRector
{
    /**
     * @var int[]
     */
    private const FUNCTIONS_WITH_CALLABLE_ARGUMENT_POSITION = [
        'array_filter' => 1,
        'array_map' => 0,
        'array_walk' => 1,
        'array_reduce' => 1,
        'usort' => 1,
        'uksort' => 1,
        'uasort' => 1,
        'array_walk_recursive' => 1,
    ];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Changes redundant anonymous bool functions to simple calls', [
            new CodeSample(
                <<<'PHP'
$paths = array_filter($paths, function ($path): bool {
    return is_dir($path);
});
PHP
                ,
                'array_filter($paths, "is_dir");'
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
        foreach (self::FUNCTIONS_WITH_CALLABLE_ARGUMENT_POSITION as $function => $callablePosition) {
            if (! $this->isName($node, $function)) {
                continue;
            }

            if (! isset($node->args[$callablePosition])) {
                continue;
            }

            if (! $node->args[$callablePosition]->value instanceof Closure) {
                continue;
            }

            /** @var Closure $closureNode */
            $closureNode = $node->args[$callablePosition]->value;

            $funcCallName = $this->matchUselessClosureFuncCallName($closureNode);
            if (! $funcCallName) {
                continue;
            }

            $node->args[$callablePosition] = new Arg(new String_($funcCallName));

            return $node;
        }

        return null;
    }

    private function matchUselessClosureFuncCallName(Closure $closure): ?string
    {
        // too complicated
        if (! $closure->stmts[0] instanceof Return_) {
            return null;
        }

        /** @var Return_ $returnNode */
        $returnNode = $closure->stmts[0];
        if (! $returnNode->expr instanceof FuncCall) {
            return null;
        }

        /** @var FuncCall $funcCallNode */
        $funcCallNode = $returnNode->expr;
        if (! $this->areNodesEqual($closure->params, $returnNode->expr->args)) {
            return null;
        }

        return $this->getName($funcCallNode);
    }
}
