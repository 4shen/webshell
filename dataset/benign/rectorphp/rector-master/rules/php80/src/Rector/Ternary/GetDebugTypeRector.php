<?php

declare(strict_types=1);

namespace Rector\Php80\Rector\Ternary;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Ternary;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see https://wiki.php.net/rfc/get_debug_type
 *
 * @see \Rector\Php80\Tests\Rector\Ternary\GetDebugTypeRector\GetDebugTypeRectorTest
 */
final class GetDebugTypeRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change ternary type resolve to get_debug_type()', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run($value)
    {
        return is_object($value) ? get_class($value) : gettype($value);
    }
}
PHP
,
                <<<'PHP'
class SomeClass
{
    public function run($value)
    {
        return get_debug_type($value);
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
        if ($this->shouldSkip($node)) {
            return null;
        }

        if (! $this->areValuesIdentical($node)) {
            return null;
        }

        /** @var FuncCall $funcCall */
        $funcCall = $node->if;
        $firstExpr = $funcCall->args[0]->value;

        return $this->createFuncCall('get_debug_type', [$firstExpr]);
    }

    private function shouldSkip(Ternary $ternary): bool
    {
        if (! $this->isFuncCallName($ternary->cond, 'is_object')) {
            return true;
        }

        if ($ternary->if === null) {
            return true;
        }

        if (! $this->isFuncCallName($ternary->if, 'get_class')) {
            return true;
        }

        return ! $this->isFuncCallName($ternary->else, 'gettype');
    }

    private function areValuesIdentical(Ternary $ternary): bool
    {
        /** @var FuncCall $isObjectFuncCall */
        $isObjectFuncCall = $ternary->cond;
        $firstExpr = $isObjectFuncCall->args[0]->value;

        /** @var FuncCall $getClassFuncCall */
        $getClassFuncCall = $ternary->if;
        $secondExpr = $getClassFuncCall->args[0]->value;

        /** @var FuncCall $gettypeFuncCall */
        $gettypeFuncCall = $ternary->else;
        $thirdExpr = $gettypeFuncCall->args[0]->value;

        if (! $this->areNodesEqual($firstExpr, $secondExpr)) {
            return false;
        }

        return $this->areNodesEqual($firstExpr, $thirdExpr);
    }
}
