<?php

declare(strict_types=1);

namespace Rector\Php71\Rector\List_;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\List_;
use PhpParser\Node\Stmt\Foreach_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see https://wiki.php.net/rfc/short_list_syntax
 * @see https://www.php.net/manual/en/migration71.new-features.php#migration71.new-features.symmetric-array-destructuring
 */

/**
 * @see \Rector\Php71\Tests\Rector\List_\ListToArrayDestructRector\ListToArrayDestructRectorTest
 */
final class ListToArrayDestructRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove & from new &X', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        list($id1, $name1) = $data;

        foreach ($data as list($id, $name)) {
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
        [$id1, $name1] = $data;

        foreach ($data as [$id, $name]) {
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
        return [List_::class];
    }

    /**
     * @param List_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isAtLeastPhpVersion(PhpVersionFeature::ARRAY_DESTRUCT)) {
            return null;
        }

        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);

        if ($parentNode instanceof Assign && $parentNode->var === $node) {
            return new Array_((array) $node->items);
        }

        if ($parentNode instanceof Foreach_ && $parentNode->valueVar === $node) {
            return new Array_((array) $node->items);
        }

        return null;
    }
}
