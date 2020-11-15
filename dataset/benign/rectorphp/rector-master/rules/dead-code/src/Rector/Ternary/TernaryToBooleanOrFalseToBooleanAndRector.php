<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\Ternary;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\Ternary;
use PHPStan\Type\BooleanType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\DeadCode\Tests\Rector\Ternary\TernaryToBooleanOrFalseToBooleanAndRector\TernaryToBooleanOrFalseToBooleanAndRectorTest
 */
final class TernaryToBooleanOrFalseToBooleanAndRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change ternary of bool : false to && bool', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function go()
    {
        return $value ? $this->getBool() : false;
    }

    private function getBool(): bool
    {
        return (bool) 5;
    }
}
PHP
,
                <<<'PHP'
class SomeClass
{
    public function go()
    {
        return $value && $this->getBool();
    }

    private function getBool(): bool
    {
        return (bool) 5;
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
        return [Ternary::class];
    }

    /**
     * @param Ternary $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->if === null) {
            return null;
        }

        if (! $this->isFalse($node->else)) {
            return null;
        }

        if ($this->isTrue($node->if)) {
            return null;
        }

        $ifType = $this->getStaticType($node->if);
        if (! $ifType instanceof BooleanType) {
            return null;
        }

        return new BooleanAnd($node->cond, $node->if);
    }
}
