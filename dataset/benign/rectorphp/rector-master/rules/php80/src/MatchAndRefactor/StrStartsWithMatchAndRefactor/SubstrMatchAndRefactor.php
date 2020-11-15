<?php

declare(strict_types=1);

namespace Rector\Php80\MatchAndRefactor\StrStartsWithMatchAndRefactor;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\FuncCall;
use Rector\Php80\Contract\StrStartWithMatchAndRefactorInterface;
use Rector\Php80\ValueObject\StrStartsWithValueObject;

final class SubstrMatchAndRefactor extends AbstractMatchAndRefactor implements StrStartWithMatchAndRefactorInterface
{
    /**
     * @param Identical|NotIdentical $binaryOp
     */
    public function match(BinaryOp $binaryOp): ?StrStartsWithValueObject
    {
        $isPositive = $binaryOp instanceof Identical;

        if ($this->isFuncCallName($binaryOp->left, 'substr')) {
            /** @var FuncCall $funcCall */
            $funcCall = $binaryOp->left;
            $haystack = $funcCall->args[0]->value;

            return new StrStartsWithValueObject($funcCall, $haystack, $binaryOp->right, $isPositive);
        }

        if ($this->isFuncCallName($binaryOp->right, 'substr')) {
            /** @var FuncCall $funcCall */
            $funcCall = $binaryOp->right;
            $haystack = $funcCall->args[0]->value;

            return new StrStartsWithValueObject($funcCall, $haystack, $binaryOp->left, $isPositive);
        }

        return null;
    }

    public function refactor(StrStartsWithValueObject $strStartsWithValueObject): ?Node
    {
        $substrFuncCall = $strStartsWithValueObject->getFuncCall();
        if (! $this->valueResolver->isValue($substrFuncCall->args[1]->value, 0)) {
            return null;
        }

        if (! $this->isFuncCallName($substrFuncCall->args[2]->value, 'strlen')) {
            return null;
        }

        /** @var FuncCall $strlenFuncCall */
        $strlenFuncCall = $substrFuncCall->args[2]->value;
        $needleExpr = $strlenFuncCall->args[0]->value;

        $comparedNeedleExpr = $strStartsWithValueObject->getNeedleExpr();
        if (! $this->betterStandardPrinter->areNodesEqual($needleExpr, $comparedNeedleExpr)) {
            return null;
        }

        return $this->createStrStartsWith($strStartsWithValueObject);
    }
}
