<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use Rector\Core\PhpParser\Node\Manipulator\IfManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\DeadCode\NodeManipulator\CountManipulator;
use Rector\DeadCode\UselessIfCondBeforeForeachDetector;

/**
 * @see \Rector\DeadCode\Tests\Rector\If_\RemoveUnusedNonEmptyArrayBeforeForeachRector\RemoveUnusedNonEmptyArrayBeforeForeachRectorTest
 */
final class RemoveUnusedNonEmptyArrayBeforeForeachRector extends AbstractRector
{
    /**
     * @var IfManipulator
     */
    private $ifManipulator;

    /**
     * @var UselessIfCondBeforeForeachDetector
     */
    private $uselessIfCondBeforeForeachDetector;

    /**
     * @var CountManipulator
     */
    private $countManipulator;

    public function __construct(
        IfManipulator $ifManipulator,
        UselessIfCondBeforeForeachDetector $uselessIfCondBeforeForeachDetector,
        CountManipulator $countManipulator
    ) {
        $this->ifManipulator = $ifManipulator;
        $this->uselessIfCondBeforeForeachDetector = $uselessIfCondBeforeForeachDetector;
        $this->countManipulator = $countManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove unused if check to non-empty array before foreach of the array', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        $values = [];
        if ($values !== []) {
            foreach ($values as $value) {
                echo $value;
            }
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
        $values = [];
        foreach ($values as $value) {
            echo $value;
        }
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
        if (! $this->isUselessBeforeForeachCheck($node)) {
            return null;
        }

        return $node->stmts[0];
    }

    private function isUselessBeforeForeachCheck(If_ $if): bool
    {
        if (! $this->ifManipulator->isIfWithOnlyForeach($if)) {
            return false;
        }

        /** @var Foreach_ $foreach */
        $foreach = $if->stmts[0];
        $foreachExpr = $foreach->expr;

        if ($this->uselessIfCondBeforeForeachDetector->isMatchingNotIdenticalEmptyArray($if, $foreachExpr)) {
            return true;
        }

        if ($this->uselessIfCondBeforeForeachDetector->isMatchingNotEmpty($if, $foreachExpr)) {
            return true;
        }

        return $this->countManipulator->isCounterHigherThanOne($if->cond, $foreachExpr);
    }
}
