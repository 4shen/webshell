<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\NodeVisitorAbstract;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class StatementNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @var Stmt|null
     */
    private $previousStmt;

    /**
     * @param Node[] $nodes
     * @return Node[]|null
     */
    public function beforeTraverse(array $nodes): ?array
    {
        $this->previousStmt = null;

        return null;
    }

    /**
     * @return int|Node|void|null
     */
    public function enterNode(Node $node)
    {
        $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
        if ($parent === null) {
            if (! $node instanceof Stmt) {
                throw new ShouldNotHappenException('Only statement can appear at top level');
            }

            $node->setAttribute(AttributeKey::PREVIOUS_STATEMENT, $this->previousStmt);
            $node->setAttribute(AttributeKey::CURRENT_STATEMENT, $node);
            $this->previousStmt = $node;
        }

        if (isset($node->stmts)) {
            $previous = $node;
            foreach ((array) $node->stmts as $stmt) {
                $stmt->setAttribute(AttributeKey::PREVIOUS_STATEMENT, $previous);
                $stmt->setAttribute(AttributeKey::CURRENT_STATEMENT, $stmt);
                $previous = $stmt;
            }
        }
        if ($parent && ! $node->getAttribute(AttributeKey::CURRENT_STATEMENT)) {
            $node->setAttribute(
                AttributeKey::PREVIOUS_STATEMENT,
                $parent->getAttribute(AttributeKey::PREVIOUS_STATEMENT)
            );
            $node->setAttribute(
                AttributeKey::CURRENT_STATEMENT,
                $parent->getAttribute(AttributeKey::CURRENT_STATEMENT)
            );
        }
    }
}
