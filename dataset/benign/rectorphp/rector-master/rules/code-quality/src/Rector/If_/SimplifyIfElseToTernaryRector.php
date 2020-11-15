<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\If_;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\CodeQuality\Tests\Rector\If_\SimplifyIfElseToTernaryRector\SimplifyIfElseToTernaryRectorTest
 */
final class SimplifyIfElseToTernaryRector extends AbstractRector
{
    /**
     * @var int
     */
    private const LINE_LENGHT_LIMIT = 120;

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Changes if/else for same value as assign to ternary', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        if (empty($value)) {
            $this->arrayBuilt[][$key] = true;
        } else {
            $this->arrayBuilt[][$key] = $value;
        }
    }
}
PHP
                ,
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        $this->arrayBuilt[][$key] = empty($value) ? true : $value;
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
        if ($node->else === null) {
            return null;
        }

        if (count($node->elseifs) > 0) {
            return null;
        }

        $ifAssignVar = $this->resolveOnlyStmtAssignVar($node->stmts);
        $elseAssignVar = $this->resolveOnlyStmtAssignVar($node->else->stmts);

        if ($ifAssignVar === null || $elseAssignVar === null) {
            return null;
        }

        if (! $this->areNodesEqual($ifAssignVar, $elseAssignVar)) {
            return null;
        }

        $ternaryIf = $this->resolveOnlyStmtAssignExpr($node->stmts);
        $ternaryElse = $this->resolveOnlyStmtAssignExpr($node->else->stmts);
        if ($ternaryIf === null || $ternaryElse === null) {
            return null;
        }

        // has nested ternary → skip, it's super hard to read
        if ($this->haveNestedTernary([$node->cond, $ternaryIf, $ternaryElse])) {
            return null;
        }

        $ternary = new Ternary($node->cond, $ternaryIf, $ternaryElse);
        $assign = new Assign($ifAssignVar, $ternary);

        // do not create super long lines
        if ($this->isNodeTooLong($assign)) {
            return null;
        }

        return $assign;
    }

    /**
     * @param Stmt[] $stmts
     */
    private function resolveOnlyStmtAssignVar(array $stmts): ?Expr
    {
        if (count($stmts) !== 1) {
            return null;
        }

        $onlyStmt = $this->unwrapExpression($stmts[0]);
        if (! $onlyStmt instanceof Assign) {
            return null;
        }

        return $onlyStmt->var;
    }

    /**
     * @param Stmt[] $stmts
     */
    private function resolveOnlyStmtAssignExpr(array $stmts): ?Expr
    {
        if (count($stmts) !== 1) {
            return null;
        }

        $onlyStmt = $this->unwrapExpression($stmts[0]);
        if (! $onlyStmt instanceof Assign) {
            return null;
        }

        return $onlyStmt->expr;
    }

    /**
     * @param Node[] $nodes
     */
    private function haveNestedTernary(array $nodes): bool
    {
        foreach ($nodes as $node) {
            if ($this->betterNodeFinder->findInstanceOf($node, Ternary::class) !== []) {
                return true;
            }
        }

        return false;
    }

    private function isNodeTooLong(Assign $assign): bool
    {
        return Strings::length($this->print($assign)) > self::LINE_LENGHT_LIMIT;
    }

    private function unwrapExpression(Node $node): Node
    {
        return $node instanceof Expression ? $node->expr : $node;
    }
}
