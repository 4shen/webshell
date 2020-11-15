<?php

declare(strict_types=1);

namespace Rector\Php74\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignOp\Coalesce as AssignCoalesce;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\ValueObject\PhpVersionFeature;

/**
 * @see https://wiki.php.net/rfc/null_coalesce_equal_operator
 * @see \Rector\Php74\Tests\Rector\Assign\NullCoalescingOperatorRector\NullCoalescingOperatorRectorTest
 */
final class NullCoalescingOperatorRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Use null coalescing operator ??=', [
            new CodeSample(
                <<<'PHP'
$array = [];
$array['user_id'] = $array['user_id'] ?? 'value';
PHP
                ,
                <<<'PHP'
$array = [];
$array['user_id'] ??= 'value';
PHP
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Assign::class];
    }

    /**
     * @param Assign $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isAtLeastPhpVersion(PhpVersionFeature::NULL_COALESCE_ASSIGN)) {
            return null;
        }

        if (! $node->expr instanceof Coalesce) {
            return null;
        }

        if (! $this->areNodesEqual($node->var, $node->expr->left)) {
            return null;
        }

        return new AssignCoalesce($node->var, $node->expr->right);
    }
}
