<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\Foreach_;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\PhpParser\Node\Manipulator\ForeachManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see https://3v4l.org/bfsdY
 *
 * @see \Rector\CodeQuality\Tests\Rector\Foreach_\SimplifyForeachToCoalescingRector\SimplifyForeachToCoalescingRectorTest
 */
final class SimplifyForeachToCoalescingRector extends AbstractRector
{
    /**
     * @var Return_|null
     */
    private $returnNode;

    /**
     * @var ForeachManipulator
     */
    private $foreachManipulator;

    public function __construct(ForeachManipulator $foreachManipulator)
    {
        $this->foreachManipulator = $foreachManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Changes foreach that returns set value to ??', [
            new CodeSample(
                <<<'PHP'
foreach ($this->oldToNewFunctions as $oldFunction => $newFunction) {
    if ($currentFunction === $oldFunction) {
        return $newFunction;
    }
}

return null;
PHP
                ,
                <<<'PHP'
return $this->oldToNewFunctions[$currentFunction] ?? null;
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
        if (! $this->isAtLeastPhpVersion(PhpVersionFeature::NULL_COALESCE)) {
            return null;
        }

        $this->returnNode = null;

        if ($node->keyVar === null) {
            return null;
        }

        /** @var Return_|Assign|null $returnOrAssignNode */
        $returnOrAssignNode = $this->matchReturnOrAssignNode($node);
        if ($returnOrAssignNode === null) {
            return null;
        }

        // return $newValue;
        // we don't return the node value
        if (! $this->areNodesEqual($node->valueVar, $returnOrAssignNode->expr)) {
            return null;
        }

        if ($returnOrAssignNode instanceof Return_) {
            return $this->processForeachNodeWithReturnInside($node, $returnOrAssignNode);
        }

        return $this->processForeachNodeWithAssignInside($node, $returnOrAssignNode);
    }

    /**
     * @return Assign|Return_|null
     */
    private function matchReturnOrAssignNode(Foreach_ $foreachNode): ?Node
    {
        return $this->foreachManipulator->matchOnlyStmt($foreachNode, function (Node $node): ?Node {
            if (! $node instanceof If_) {
                return null;
            }

            if (! $node->cond instanceof Identical) {
                return null;
            }

            if (count($node->stmts) !== 1) {
                return null;
            }

            $innerNode = $node->stmts[0] instanceof Expression ? $node->stmts[0]->expr : $node->stmts[0];

            if ($innerNode instanceof Assign || $innerNode instanceof Return_) {
                return $innerNode;
            }

            return null;
        });
    }

    private function processForeachNodeWithReturnInside(Foreach_ $foreachNode, Return_ $returnNode): ?Node
    {
        if (! $this->areNodesEqual($foreachNode->valueVar, $returnNode->expr)) {
            return null;
        }

        /** @var If_ $ifNode */
        $ifNode = $foreachNode->stmts[0];

        /** @var Identical $identicalNode */
        $identicalNode = $ifNode->cond;

        if ($this->areNodesEqual($identicalNode->left, $foreachNode->keyVar)) {
            $checkedNode = $identicalNode->right;
        } elseif ($this->areNodesEqual($identicalNode->right, $foreachNode->keyVar)) {
            $checkedNode = $identicalNode->left;
        } else {
            return null;
        }

        // is next node Return?
        if ($foreachNode->getAttribute(AttributeKey::NEXT_NODE) instanceof Return_) {
            $this->returnNode = $foreachNode->getAttribute(AttributeKey::NEXT_NODE);
            $this->removeNode($this->returnNode);
        }

        $coalesceNode = new Coalesce(new ArrayDimFetch(
            $foreachNode->expr,
            $checkedNode
        ), $this->returnNode && $this->returnNode->expr !== null ? $this->returnNode->expr : $checkedNode);

        if ($this->returnNode !== null) {
            return new Return_($coalesceNode);
        }

        return null;
    }

    private function processForeachNodeWithAssignInside(Foreach_ $foreachNode, Assign $assign): ?Node
    {
        /** @var If_ $ifNode */
        $ifNode = $foreachNode->stmts[0];

        /** @var Identical $identicalNode */
        $identicalNode = $ifNode->cond;

        if ($this->areNodesEqual($identicalNode->left, $foreachNode->keyVar)) {
            $checkedNode = $assign->var;
            $keyNode = $identicalNode->right;
        } elseif ($this->areNodesEqual($identicalNode->right, $foreachNode->keyVar)) {
            $checkedNode = $assign->var;
            $keyNode = $identicalNode->left;
        } else {
            return null;
        }

        $arrayDimFetchNode = new ArrayDimFetch($foreachNode->expr, $keyNode);

        return new Assign($checkedNode, new Coalesce($arrayDimFetchNode, $checkedNode));
    }
}
