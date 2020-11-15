<?php

declare(strict_types=1);

namespace Rector\Celebrity\Rector\NotEqual;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\NotEqual;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see https://stackoverflow.com/a/4294663/1348344
 * @see \Rector\Celebrity\Tests\Rector\NotEqual\CommonNotEqualRector\CommonNotEqualRectorTest
 */
final class CommonNotEqualRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Use common != instead of less known <> with same meaning', [
            new CodeSample(
                <<<'PHP'
final class SomeClass
{
    public function run($one, $two)
    {
        return $one <> $two;
    }
}
PHP
                ,
                <<<'PHP'
final class SomeClass
{
    public function run($one, $two)
    {
        return $one != $two;
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
        return [NotEqual::class];
    }

    /**
     * @param NotEqual $node
     */
    public function refactor(Node $node): ?Node
    {
        // invoke override to default "!="
        $node->setAttribute(AttributeKey::ORIGINAL_NODE, null);

        return $node;
    }
}
