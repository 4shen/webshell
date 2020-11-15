<?php

declare(strict_types=1);

namespace Rector\Php72\Rector\Each;

use PhpParser\BuilderHelpers;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\List_;
use PhpParser\Node\Stmt\While_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @sponsor Thanks https://twitter.com/afilina & Zenika (CAN) for sponsoring this rule - visit them on https://zenika.ca/en/en
 *
 * @see \Rector\Php72\Tests\Rector\Each\ReplaceEachAssignmentWithKeyCurrentRector\ReplaceEachAssignmentWithKeyCurrentRectorTest
 */
final class ReplaceEachAssignmentWithKeyCurrentRector extends AbstractRector
{
    /**
     * @var string
     */
    private const KEY = 'key';

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replace each() assign outside loop', [
            new CodeSample(
                <<<'CODE_SAMPLE'
$array = ['b' => 1, 'a' => 2];
$eachedArray = each($array);
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
$array = ['b' => 1, 'a' => 2];
$eachedArray[1] = current($array);
$eachedArray['value'] = current($array);
$eachedArray[0] = key($array);
$eachedArray['key'] = key($array);
next($array);
CODE_SAMPLE
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
        if ($this->shouldSkip($node)) {
            return null;
        }

        /** @var FuncCall $eachFuncCall */
        $eachFuncCall = $node->expr;
        $eachedVariable = $eachFuncCall->args[0]->value;

        $assignVariable = $node->var;

        $newNodes = $this->createNewNodes($assignVariable, $eachedVariable);

        $this->addNodesAfterNode($newNodes, $node);

        $this->removeNode($node);

        return null;
    }

    private function shouldSkip(Assign $assign): bool
    {
        if (! $this->isFuncCallName($assign->expr, 'each')) {
            return true;
        }

        $parentNode = $assign->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode instanceof While_) {
            return true;
        }

        // skip assign to List
        return $parentNode instanceof Assign && $parentNode->var instanceof List_;
    }

    /**
     * @return Node[]
     */
    private function createNewNodes(Expr $assignVariable, Expr $eachedVariable): array
    {
        $newNodes = [];

        $newNodes[] = $this->createDimFetchAssignWithFuncCall($assignVariable, $eachedVariable, 1, 'current');
        $newNodes[] = $this->createDimFetchAssignWithFuncCall($assignVariable, $eachedVariable, 'value', 'current');

        $newNodes[] = $this->createDimFetchAssignWithFuncCall($assignVariable, $eachedVariable, 0, self::KEY);
        $newNodes[] = $this->createDimFetchAssignWithFuncCall($assignVariable, $eachedVariable, self::KEY, self::KEY);

        $newNodes[] = $this->createFuncCall('next', [new Arg($eachedVariable)]);

        return $newNodes;
    }

    /**
     * @param string|int $dimValue
     */
    private function createDimFetchAssignWithFuncCall(
        Expr $assignVariable,
        Expr $eachedVariable,
        $dimValue,
        string $functionName
    ): Assign {
        $dim = BuilderHelpers::normalizeValue($dimValue);
        $arrayDimFetch = new ArrayDimFetch($assignVariable, $dim);

        return new Assign($arrayDimFetch, $this->createFuncCall($functionName, [new Arg($eachedVariable)]));
    }
}
