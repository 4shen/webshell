<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\TryCatch;

use PhpParser\Node;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\Node\Stmt\Throw_;
use PhpParser\Node\Stmt\TryCatch;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\DeadCode\Tests\Rector\TryCatch\RemoveDeadTryCatchRector\RemoveDeadTryCatchRectorTest
 */
final class RemoveDeadTryCatchRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove dead try/catch', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        try {
            // some code
        }
        catch (Throwable $throwable) {
            throw $throwable;
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
        // some code
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
        return [TryCatch::class];
    }

    /**
     * @param TryCatch $node
     */
    public function refactor(Node $node): ?Node
    {
        if (count($node->catches) !== 1) {
            return null;
        }

        /** @var Catch_ $onlyCatch */
        $onlyCatch = $node->catches[0];
        if (count($onlyCatch->stmts) !== 1) {
            return null;
        }

        $onlyCatchStmt = $onlyCatch->stmts[0];
        if (! $onlyCatchStmt instanceof Throw_) {
            return null;
        }

        if (! $this->areNodesEqual($onlyCatch->var, $onlyCatchStmt->expr)) {
            return null;
        }

        foreach ($node->stmts as $tryStmt) {
            $this->addNodeAfterNode($tryStmt, $node);
        }

        $this->removeNode($node);

        return null;
    }
}
