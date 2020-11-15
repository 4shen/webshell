<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Stmt\If_;
use PHPStan\Type\Constant\ConstantBooleanType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\DeadCode\Tests\Rector\If_\RemoveAlwaysTrueIfConditionRector\RemoveAlwaysTrueIfConditionRectorTest
 */
final class RemoveAlwaysTrueIfConditionRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove if condition that is always true', [
            new CodeSample(
                <<<'PHP'
final class SomeClass
{
    public function go()
    {
        if (1 === 1) {
            return 'yes';
        }
        
        return 'no';
    }
}
PHP
                ,
                <<<'PHP'
final class SomeClass
{
    public function go()
    {
        return 'yes';
        
        return 'no';
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
        if ($node->else !== null) {
            return null;
        }

        // just one if
        if (count($node->elseifs) !== 0) {
            return null;
        }

        $conditionStaticType = $this->getStaticType($node->cond);
        if (! $conditionStaticType instanceof ConstantBooleanType) {
            return null;
        }

        if (! $conditionStaticType->getValue()) {
            return null;
        }

        if (count($node->stmts) !== 1) {
            // unable to handle now
            return null;
        }

        return $node->stmts[0];
    }
}
