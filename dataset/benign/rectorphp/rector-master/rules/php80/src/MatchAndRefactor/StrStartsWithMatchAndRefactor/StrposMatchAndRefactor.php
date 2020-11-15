<?php

declare(strict_types=1);

namespace Rector\Php80\MatchAndRefactor\StrStartsWithMatchAndRefactor;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use Rector\Php80\Contract\StrStartWithMatchAndRefactorInterface;
use Rector\Php80\ValueObject\StrStartsWithValueObject;

final class StrposMatchAndRefactor extends AbstractMatchAndRefactor implements StrStartWithMatchAndRefactorInterface
{
    /**
     * @param Identical|NotIdentical $binaryOp
     */
    public function match(BinaryOp $binaryOp): ?StrStartsWithValueObject
    {
        $isPositive = $binaryOp instanceof Identical;

        if ($this->isFuncCallName($binaryOp->left, 'strpos')) {
            if (! $this->valueResolver->isValue($binaryOp->right, 0)) {
                return null;
            }

            /** @var FuncCall $funcCall */
            $funcCall = $binaryOp->left;
            $haystack = $funcCall->args[0]->value;
            $needle = $funcCall->args[1]->value;

            return new StrStartsWithValueObject($funcCall, $haystack, $needle, $isPositive);
        }

        if ($this->isFuncCallName($binaryOp->right, 'strpos')) {
            if (! $this->valueResolver->isValue($binaryOp->left, 0)) {
                return null;
            }

            /** @var FuncCall $funcCall */
            $funcCall = $binaryOp->right;
            $haystack = $funcCall->args[0]->value;
            $needle = $funcCall->args[1]->value;

            return new StrStartsWithValueObject($funcCall, $haystack, $needle, $isPositive);
        }

        return null;
    }

    /**
     * @return FuncCall
     */
    public function refactor(StrStartsWithValueObject $strStartsWithValueObject): ?Node
    {
        $strposFuncCall = $strStartsWithValueObject->getFuncCall();
        $strposFuncCall->name = new Name('str_starts_with');

        return $strposFuncCall;
    }
}
