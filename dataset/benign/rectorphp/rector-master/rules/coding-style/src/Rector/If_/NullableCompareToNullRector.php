<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Stmt\If_;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\StringType;
use PHPStan\Type\UnionType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\CodingStyle\Tests\Rector\If_\NullableCompareToNullRector\NullableCompareToNullRectorTest
 */
final class NullableCompareToNullRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Changes negate of empty comparison of nullable value to explicit === or !== compare',
            [
                new CodeSample(
                    <<<'PHP'
/** @var stdClass|null $value */
if ($value) {
}

if (!$value) {
}
PHP
                    ,
                    <<<'PHP'
/** @var stdClass|null $value */
if ($value !== null) {
}

if ($value === null) {
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
        return [If_::class];
    }

    /**
     * @param If_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->cond instanceof BooleanNot && $this->isNullableNonScalarType($node->cond->expr)) {
            $node->cond = new Identical($node->cond->expr, $this->createNull());

            return $node;
        }

        if ($this->isNullableNonScalarType($node->cond)) {
            $node->cond = new NotIdentical($node->cond, $this->createNull());

            return $node;
        }

        return null;
    }

    private function isNullableNonScalarType(Node $node): bool
    {
        $staticType = $this->getStaticType($node);
        if ($staticType instanceof MixedType) {
            return false;
        }

        if (! $staticType instanceof UnionType) {
            return false;
        }

        // is non-nullable?
        if ($staticType->isSuperTypeOf(new NullType())->no()) {
            return false;
        }

        // is array?
        foreach ($staticType->getTypes() as $subType) {
            if ($subType instanceof ArrayType) {
                return false;
            }
        }

        // is string?
        if ($staticType->isSuperTypeOf(new StringType())->yes()) {
            return false;
        }

        // is number?
        if ($staticType->isSuperTypeOf(new IntegerType())->yes()) {
            return false;
        }

        // is bool?
        if ($staticType->isSuperTypeOf(new BooleanType())->yes()) {
            return false;
        }

        return ! $staticType->isSuperTypeOf(new FloatType())->yes();
    }
}
