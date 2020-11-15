<?php

declare(strict_types=1);

namespace Rector\Php80\Rector\Catch_;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Catch_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see https://wiki.php.net/rfc/non-capturing_catches
 *
 * @see \Rector\Php80\Tests\Rector\Catch_\RemoveUnusedVariableInCatchRector\RemoveUnusedVariableInCatchRectorTest
 */
final class RemoveUnusedVariableInCatchRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove unused variable in catch()', [
            new CodeSample(
                <<<'PHP'
final class SomeClass
{
    public function run()
    {
        try {
        } catch (Throwable $notUsedThrowable) {
        }
    }
}
PHP
,
                <<<'PHP'
final class SomeClass
{
    public function run()
    {
        try {
        } catch (Throwable) {
        }
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
        return [Catch_::class];
    }

    /**
     * @param Catch_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $caughtVar = $node->var;
        if ($caughtVar === null) {
            return null;
        }

        if ($this->isVariableUsed((array) $node->stmts, $caughtVar)) {
            return null;
        }

        $node->var = null;

        return $node;
    }

    /**
     * @param Node[] $nodes
     */
    private function isVariableUsed(array $nodes, Variable $variable): bool
    {
        return (bool) $this->betterNodeFinder->findFirst($nodes, function (Node $node) use ($variable) {
            if (! $node instanceof Variable) {
                return false;
            }

            return $this->areNodesEqual($node, $variable);
        });
    }
}
