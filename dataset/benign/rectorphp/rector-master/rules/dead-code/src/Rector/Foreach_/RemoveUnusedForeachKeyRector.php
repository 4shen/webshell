<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\Foreach_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Foreach_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\DeadCode\Tests\Rector\Foreach_\RemoveUnusedForeachKeyRector\RemoveUnusedForeachKeyRectorTest
 */
final class RemoveUnusedForeachKeyRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove unused key in foreach', [
            new CodeSample(
                <<<'PHP'
$items = [];
foreach ($items as $key => $value) {
    $result = $value;
}
PHP
                ,
                <<<'PHP'
$items = [];
foreach ($items as $value) {
    $result = $value;
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
        return [Foreach_::class];
    }

    /**
     * @param Foreach_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->keyVar === null) {
            return null;
        }

        if ($this->isNodeUsedIn($node->keyVar, $node->stmts)) {
            return null;
        }

        $node->keyVar = null;

        return $node;
    }
}
