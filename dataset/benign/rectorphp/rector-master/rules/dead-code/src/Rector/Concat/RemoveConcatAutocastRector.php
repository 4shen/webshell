<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\Concat;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\Cast\String_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\DeadCode\Tests\Rector\Concat\RemoveConcatAutocastRector\RemoveConcatAutocastRectorTest
 */
final class RemoveConcatAutocastRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove (string) casting when it comes to concat, that does this by default', [
            new CodeSample(
                <<<'PHP'
class SomeConcatingClass
{
    public function run($value)
    {
        return 'hi ' . (string) $value;
    }
}
PHP
                ,
                <<<'PHP'
class SomeConcatingClass
{
    public function run($value)
    {
        return 'hi ' . $value;
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
        return [Concat::class];
    }

    /**
     * @param Concat $node
     */
    public function refactor(Node $node): ?Node
    {
        $node->left = $this->removeStringCast($node->left);
        $node->right = $this->removeStringCast($node->right);

        return $node;
    }

    private function removeStringCast(Expr $expr): Expr
    {
        return $expr instanceof String_ ? $expr->expr : $expr;
    }
}
