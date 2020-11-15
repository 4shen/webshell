<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\DeadCode\Tests\Rector\FunctionLike\RemoveDeadReturnRector\RemoveDeadReturnRectorTest
 */
final class RemoveDeadReturnRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove last return in the functions, since does not do anything', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        $shallWeDoThis = true;

        if ($shallWeDoThis) {
            return;
        }

        return;
    }
}
PHP
                ,
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        $shallWeDoThis = true;

        if ($shallWeDoThis) {
            return;
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
        return [FunctionLike::class];
    }

    /**
     * @param ClassMethod|Function_|Closure $node
     */
    public function refactor(Node $node): ?Node
    {
        if (empty($node->stmts)) {
            return null;
        }

        $stmtValues = array_values($node->stmts);
        $lastStmt = end($stmtValues);
        if (! $lastStmt instanceof Return_) {
            return null;
        }

        if ($lastStmt->expr !== null) {
            return null;
        }

        $this->removeNode($lastStmt);

        return $node;
    }
}
