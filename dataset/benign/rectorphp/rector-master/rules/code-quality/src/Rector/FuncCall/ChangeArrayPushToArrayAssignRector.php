<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see https://stackoverflow.com/questions/559844/whats-better-to-use-in-php-array-value-or-array-pusharray-value
 *
 * @see \Rector\CodeQuality\Tests\Rector\FuncCall\ChangeArrayPushToArrayAssignRector\ChangeArrayPushToArrayAssignRectorTest
 */
final class ChangeArrayPushToArrayAssignRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change array_push() to direct variable assign', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        $items = [];
        array_push($items, $item);
    }
}
PHP
,
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        $items = [];
        $items[] = $item;
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
        return [FuncCall::class];
    }

    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isName($node, 'array_push')) {
            return null;
        }

        if ($this->hasArraySpread($node)) {
            return null;
        }

        $arrayDimFetch = new ArrayDimFetch($node->args[0]->value);

        $position = 1;
        while (isset($node->args[$position])) {
            $assign = new Assign($arrayDimFetch, $node->args[$position]->value);

            // keep comments of first line
            if ($position === 1) {
                $assign = new Expression($assign);
                $assign->setAttribute(AttributeKey::COMMENTS, $node->getComments());
            }

            $this->addNodeAfterNode($assign, $node);

            ++$position;
        }

        $this->removeNode($node);

        return null;
    }

    private function hasArraySpread(FuncCall $funcCall): bool
    {
        foreach ($funcCall->args as $arg) {
            /** @var Arg $arg */
            if ($arg->unpack) {
                return true;
            }
        }

        return false;
    }
}
