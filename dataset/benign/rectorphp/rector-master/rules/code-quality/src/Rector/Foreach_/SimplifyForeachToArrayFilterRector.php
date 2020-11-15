<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\Foreach_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\CodeQuality\Tests\Rector\Foreach_\SimplifyForeachToArrayFilterRector\SimplifyForeachToArrayFilterRectorTest
 */
final class SimplifyForeachToArrayFilterRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Simplify foreach with function filtering to array filter', [
            new CodeSample(
                <<<'PHP'
$directories = [];
$possibleDirectories = [];
foreach ($possibleDirectories as $possibleDirectory) {
    if (file_exists($possibleDirectory)) {
        $directories[] = $possibleDirectory;
    }
}
PHP
                ,
                <<<'PHP'
$possibleDirectories = [];
$directories = array_filter($possibleDirectories, 'file_exists');
PHP
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Foreach_::class];
    }

    /**
     * @param Foreach_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        /** @var If_ $ifNode */
        $ifNode = $node->stmts[0];

        /** @var FuncCall $funcCallNode */
        $funcCallNode = $ifNode->cond;
        if (count($ifNode->stmts) !== 1) {
            return null;
        }

        if (count($funcCallNode->args) !== 1) {
            return null;
        }

        if (! $this->areNodesEqual($funcCallNode->args[0], $node->valueVar)) {
            return null;
        }

        if (! $ifNode->stmts[0] instanceof Expression) {
            return null;
        }

        $onlyNodeInIf = $ifNode->stmts[0]->expr;
        if (! $onlyNodeInIf instanceof Assign) {
            return null;
        }

        if (! $onlyNodeInIf->var instanceof ArrayDimFetch) {
            return null;
        }

        if (! $this->areNodesEqual($onlyNodeInIf->expr, $node->valueVar)) {
            return null;
        }

        $name = $this->getName($funcCallNode);
        if ($name === null) {
            return null;
        }

        return $this->createAssignNode($node, $name, $onlyNodeInIf->var);
    }

    private function shouldSkip(Foreach_ $foreachNode): bool
    {
        if (count($foreachNode->stmts) !== 1) {
            return true;
        }

        if (! $foreachNode->stmts[0] instanceof If_) {
            return true;
        }

        /** @var If_ $ifNode */
        $ifNode = $foreachNode->stmts[0];

        if ($ifNode->else !== null) {
            return true;
        }

        if ($ifNode->elseifs !== []) {
            return true;
        }

        return ! $ifNode->cond instanceof FuncCall;
    }

    private function createAssignNode(Foreach_ $foreachNode, string $name, ArrayDimFetch $arrayDimFetch): Assign
    {
        $functionNameNode = new String_($name);
        $arrayFilterFuncCall = new FuncCall(new Name('array_filter'), [
            new Arg($foreachNode->expr),
            new Arg($functionNameNode),
        ]);

        return new Assign($arrayDimFetch->var, $arrayFilterFuncCall);
    }
}
