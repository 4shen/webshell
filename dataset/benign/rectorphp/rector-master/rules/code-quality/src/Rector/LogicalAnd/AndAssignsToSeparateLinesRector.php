<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\LogicalAnd;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\LogicalAnd;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see https://3v4l.org/ji8bX
 * @see \Rector\CodeQuality\Tests\Rector\LogicalAnd\AndAssignsToSeparateLinesRector\AndAssignsToSeparateLinesRectorTest
 */
final class AndAssignsToSeparateLinesRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Split 2 assigns ands to separate line', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        $tokens = [];
        $token = 4 and $tokens[] = $token;
    }
}
PHP
                ,
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        $tokens = [];
        $token = 4;
        $tokens[] = $token;
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
        return [LogicalAnd::class];
    }

    /**
     * @param LogicalAnd $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $node->left instanceof Assign || ! $node->right instanceof Assign) {
            return null;
        }

        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parentNode instanceof Expression) {
            return null;
        }

        $this->addNodeAfterNode($node->right, $node);

        return $node->left;
    }
}
