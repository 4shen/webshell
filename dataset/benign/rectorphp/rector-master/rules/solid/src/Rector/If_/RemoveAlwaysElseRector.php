<?php

declare(strict_types=1);

namespace Rector\SOLID\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr\Exit_;
use PhpParser\Node\Stmt\Continue_;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Throw_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\SOLID\Tests\Rector\If_\RemoveAlwaysElseRector\RemoveAlwaysElseRectorTest
 */
final class RemoveAlwaysElseRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Split if statement, when if condition always break execution flow', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run($value)
    {
        if ($value) {
            throw new \InvalidStateException;
        } else {
            return 10;
        }
    }
}
PHP
,
                <<<'PHP'
class SomeClass
{
    public function run($value)
    {
        if ($value) {
            throw new \InvalidStateException;
        }

        return 10;
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
        return [If_::class];
    }

    /**
     * @param If_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->doesLastStatementBreakFlow($node)) {
            return null;
        }

        if ($node->elseifs !== []) {
            $newNode = new If_($node->cond);
            $newNode->stmts = $node->stmts;

            $this->addNodeBeforeNode($newNode, $node);

            /** @var ElseIf_ $firstElseIf */
            $firstElseIf = array_shift($node->elseifs);
            $node->cond = $firstElseIf->cond;
            $node->stmts = $firstElseIf->stmts;

            return $node;
        }

        if ($node->else !== null) {
            foreach ($node->else->stmts as $stmt) {
                $this->addNodeAfterNode($stmt, $node);
            }
            $node->else = null;
            return $node;
        }

        return null;
    }

    private function doesLastStatementBreakFlow(Node $node): bool
    {
        $lastStmt = end($node->stmts);
        return ! ($lastStmt instanceof Return_
            || $lastStmt instanceof Throw_
            || $lastStmt instanceof Continue_
            || ($lastStmt instanceof Expression && $lastStmt->expr instanceof Exit_));
    }
}
