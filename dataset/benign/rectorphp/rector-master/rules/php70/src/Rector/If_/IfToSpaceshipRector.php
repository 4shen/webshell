<?php

declare(strict_types=1);

namespace Rector\Php70\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Expr\BinaryOp\Greater;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\Smaller;
use PhpParser\Node\Expr\BinaryOp\Spaceship;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see https://wiki.php.net/rfc/combined-comparison-operator
 * @see https://3v4l.org/LPbA0
 *
 * @see \Rector\Php70\Tests\Rector\If_\IfToSpaceshipRector\IfToSpaceshipRectorTest
 */
final class IfToSpaceshipRector extends AbstractRector
{
    /**
     * @var int|null
     */
    private $onEqual;

    /**
     * @var int|null
     */
    private $onSmaller;

    /**
     * @var int|null
     */
    private $onGreater;

    /**
     * @var Expr|null
     */
    private $firstValue;

    /**
     * @var Expr|null
     */
    private $secondValue;

    /**
     * @var Node|null
     */
    private $nextNode;

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Changes if/else to spaceship <=> where useful', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        usort($languages, function ($a, $b) {
            if ($a[0] === $b[0]) {
                return 0;
            }

            return ($a[0] < $b[0]) ? 1 : -1;
        });
    }
}
PHP
                ,
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        usort($languages, function ($a, $b) {
            return $b[0] <=> $a[0];
        });
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
        return [If_::class];
    }

    /**
     * @param If_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isAtLeastPhpVersion(PhpVersionFeature::SPACESHIP)) {
            return null;
        }

        if (! $node->cond instanceof Equal && ! $node->cond instanceof Identical) {
            return null;
        }

        $this->reset();

        $this->matchOnEqualFirstValueAndSecondValue($node);

        if ($this->firstValue === null || $this->secondValue === null) {
            return null;
        }

        if (! $this->areVariablesEqual($node->cond, $this->firstValue, $this->secondValue)) {
            return null;
        }

        // is spaceship return values?
        if ([$this->onGreater, $this->onEqual, $this->onSmaller] !== [-1, 0, 1]) {
            return null;
        }

        if ($this->nextNode !== null) {
            $this->removeNode($this->nextNode);
        }

        // spaceship ready!
        $spaceshipNode = new Spaceship($this->secondValue, $this->firstValue);

        return new Return_($spaceshipNode);
    }

    private function reset(): void
    {
        $this->onEqual = null;
        $this->onSmaller = null;
        $this->onGreater = null;

        $this->firstValue = null;
        $this->secondValue = null;
    }

    private function processTernary(Ternary $ternary): void
    {
        if ($ternary->cond instanceof Smaller) {
            $this->firstValue = $ternary->cond->left;
            $this->secondValue = $ternary->cond->right;

            if ($ternary->if !== null) {
                $this->onSmaller = $this->getValue($ternary->if);
            }

            $this->onGreater = $this->getValue($ternary->else);
        } elseif ($ternary->cond instanceof Greater) {
            $this->firstValue = $ternary->cond->right;
            $this->secondValue = $ternary->cond->left;

            if ($ternary->if !== null) {
                $this->onGreater = $this->getValue($ternary->if);
            }

            $this->onSmaller = $this->getValue($ternary->else);
        }
    }

    private function areVariablesEqual(BinaryOp $binaryOp, ?Expr $firstValue, ?Expr $secondValue): bool
    {
        if ($firstValue === null || $secondValue === null) {
            return false;
        }

        if ($this->areNodesEqual($binaryOp->left, $firstValue) && $this->areNodesEqual(
            $binaryOp->right,
            $secondValue
        )) {
            return true;
        }
        return $this->areNodesEqual($binaryOp->right, $firstValue) && $this->areNodesEqual(
            $binaryOp->left,
            $secondValue
        );
    }

    private function matchOnEqualFirstValueAndSecondValue(If_ $if): void
    {
        $this->matchOnEqual($if);

        if ($if->else !== null) {
            $this->processElse($if->else);
        } else {
            $this->nextNode = $if->getAttribute(AttributeKey::NEXT_NODE);
            if ($this->nextNode instanceof Return_ && $this->nextNode->expr instanceof Ternary) {
                /** @var Ternary $ternary */
                $ternary = $this->nextNode->expr;
                $this->processTernary($ternary);
            }
        }
    }

    private function matchOnEqual(If_ $if): void
    {
        if (count($if->stmts) !== 1) {
            return;
        }

        $onlyIfStmt = $if->stmts[0];

        if ($onlyIfStmt instanceof Return_) {
            if ($onlyIfStmt->expr === null) {
                return;
            }

            $this->onEqual = $this->getValue($onlyIfStmt->expr);
        }
    }

    private function processElse(Else_ $else): void
    {
        if (count($else->stmts) !== 1) {
            return;
        }

        if (! $else->stmts[0] instanceof Return_) {
            return;
        }

        /** @var Return_ $returnNode */
        $returnNode = $else->stmts[0];
        if ($returnNode->expr instanceof Ternary) {
            $this->processTernary($returnNode->expr);
        }
    }
}
