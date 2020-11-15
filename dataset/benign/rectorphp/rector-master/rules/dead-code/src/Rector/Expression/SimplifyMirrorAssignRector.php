<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\Expression;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\DeadCode\Tests\Rector\Expression\SimplifyMirrorAssignRector\SimplifyMirrorAssignRectorTest
 */
final class SimplifyMirrorAssignRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Removes unneeded $a = $a assigns', [new CodeSample('$a = $a;', '')]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Expression::class];
    }

    /**
     * @param Expression $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $node->expr instanceof Assign) {
            return null;
        }

        /** @var Assign $assignNode */
        $assignNode = $node->expr;

        if ($this->areNodesEqual($assignNode->var, $assignNode->expr)) {
            $this->removeNode($node);
        }

        return null;
    }
}
