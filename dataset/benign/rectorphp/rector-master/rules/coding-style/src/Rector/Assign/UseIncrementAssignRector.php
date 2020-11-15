<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr\AssignOp\Minus;
use PhpParser\Node\Expr\AssignOp\Plus;
use PhpParser\Node\Expr\PreDec;
use PhpParser\Node\Expr\PreInc;
use PhpParser\Node\Scalar\LNumber;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\CodingStyle\Tests\Rector\Assign\UseIncrementAssignRector\UseIncrementAssignRectorTest
 */
final class UseIncrementAssignRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Use ++ increment instead of $var += 1.',
            [
                new CodeSample(
                    <<<'PHP'
class SomeClass
{
    public function run()
    {
        $style += 1;
    }
}
PHP
                    ,
                    <<<'PHP'
class SomeClass
{
    public function run()
    {
        ++$style
    }
}
PHP
                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Plus::class, Minus::class];
    }

    /**
     * @param Plus|Minus $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $node->expr instanceof LNumber) {
            return null;
        }

        if ($node->expr->value !== 1) {
            return null;
        }

        if ($node instanceof Plus) {
            $newNode = new PreInc($node->var);
        } else {
            $newNode = new PreDec($node->var);
        }

        return $newNode;
    }
}
