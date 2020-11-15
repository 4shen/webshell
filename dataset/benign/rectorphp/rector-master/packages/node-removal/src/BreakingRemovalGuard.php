<?php

declare(strict_types=1);

namespace Rector\NodeRemoval;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\While_;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class BreakingRemovalGuard
{
    public function ensureNodeCanBeRemove(Node $node): void
    {
        // validate the $parentNodenode can be removed
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        if ($this->isLegalNodeRemoval($node)) {
            return;
        }

        throw new ShouldNotHappenException(sprintf(
            'Node "%s" is child of "%s", so it cannot be removed as it would break PHP code. Change or remove the parent node instead.',
            get_class($node),
            /** @var Node $parentNode */
            get_class($parentNode)
        ));
    }

    public function isLegalNodeRemoval(Node $node): bool
    {
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);

        if ($parentNode instanceof BooleanNot) {
            $parentNode = $parentNode->getAttribute(AttributeKey::PARENT_NODE);
        }

        return ! $parentNode instanceof Assign && ! $this->isIfCondition($node) && ! $this->isWhileCondition($node);
    }

    private function isIfCondition(Node $node): bool
    {
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parentNode instanceof If_) {
            return false;
        }

        return $parentNode->cond === $node;
    }

    private function isWhileCondition(Node $node): bool
    {
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parentNode instanceof While_) {
            return false;
        }

        return $parentNode->cond === $node;
    }
}
