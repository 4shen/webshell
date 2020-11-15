<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\DeadCode\Tests\Rector\FunctionLike\RemoveCodeAfterReturnRector\RemoveCodeAfterReturnRectorTest
 */
final class RemoveCodeAfterReturnRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove dead code after return statement', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run(int $a)
    {
         return $a;
         $a++;
    }
}
PHP
                ,
                <<<'PHP'
class SomeClass
{
    public function run(int $a)
    {
         return $a;
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
        return [Closure::class, ClassMethod::class, Function_::class];
    }

    /**
     * @param Closure|ClassMethod|Function_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->stmts === null) {
            return null;
        }

        $isDeadAfterReturn = false;
        foreach ($node->stmts as $key => $stmt) {
            if ($isDeadAfterReturn) {
                // keep comment
                if ($node->stmts[$key] instanceof Nop) {
                    continue;
                }

                $this->removeStmt($node, $key);
            }

            if ($stmt instanceof Return_) {
                $isDeadAfterReturn = true;
                continue;
            }
        }

        return null;
    }
}
