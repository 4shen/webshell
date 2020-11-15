<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\PhpParser\Node\Manipulator\IfManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\CodeQuality\Tests\Rector\If_\SimplifyIfNotNullReturnRector\SimplifyIfNotNullReturnRectorTest
 */
final class SimplifyIfNotNullReturnRector extends AbstractRector
{
    /**
     * @var IfManipulator
     */
    private $ifManipulator;

    public function __construct(IfManipulator $ifManipulator)
    {
        $this->ifManipulator = $ifManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Changes redundant null check to instant return', [
            new CodeSample(
                <<<'PHP'
$newNode = 'something ;
if ($newNode !== null) {
    return $newNode;
}

return null;
PHP
                ,
                <<<'PHP'
$newNode = 'something ;
return $newNode;
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
        $comparedNode = $this->ifManipulator->matchIfNotNullReturnValue($node);
        if ($comparedNode !== null) {
            $insideIfNode = $node->stmts[0];

            $nextNode = $node->getAttribute(AttributeKey::NEXT_NODE);
            if (! $nextNode instanceof Return_ || $nextNode->expr === null) {
                return null;
            }

            if (! $this->isNull($nextNode->expr)) {
                return null;
            }

            $this->removeNode($nextNode);
            return $insideIfNode;
        }

        $comparedNode = $this->ifManipulator->matchIfValueReturnValue($node);
        if ($comparedNode !== null) {
            $nextNode = $node->getAttribute(AttributeKey::NEXT_NODE);
            if (! $nextNode instanceof Return_) {
                return null;
            }

            if (! $this->areNodesEqual($comparedNode, $nextNode->expr)) {
                return null;
            }

            $this->removeNode($nextNode);
            return clone $nextNode;
        }

        return null;
    }
}
