<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\Switch_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Break_;
use PhpParser\Node\Stmt\Case_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Switch_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\DeadCode\Tests\Rector\Switch_\RemoveDuplicatedCaseInSwitchRector\RemoveDuplicatedCaseInSwitchRectorTest
 */
final class RemoveDuplicatedCaseInSwitchRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('2 following switch keys with identical  will be reduced to one result', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        switch ($name) {
             case 'clearHeader':
                 return $this->modifyHeader($node, 'remove');
             case 'clearAllHeaders':
                 return $this->modifyHeader($node, 'replace');
             case 'clearRawHeaders':
                 return $this->modifyHeader($node, 'replace');
             case '...':
                 return 5;
        }
    }
}
PHP
                ,
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        switch ($name) {
             case 'clearHeader':
                 return $this->modifyHeader($node, 'remove');
             case 'clearAllHeaders':
             case 'clearRawHeaders':
                 return $this->modifyHeader($node, 'replace');
             case '...':
                 return 5;
        }
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
        return [Switch_::class];
    }

    /**
     * @param Switch_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (count($node->cases) < 2) {
            return null;
        }

        /** @var Case_|null $previousCase */
        $previousCase = null;
        foreach ($node->cases as $case) {
            if ($previousCase && $this->areSwitchStmtsEqualsAndWithBreak($case, $previousCase)) {
                $previousCase->stmts = [];
            }

            $previousCase = $case;
        }

        return $node;
    }

    private function areSwitchStmtsEqualsAndWithBreak(Case_ $currentCase, Case_ $previousCase): bool
    {
        if (! $this->areNodesEqual($currentCase->stmts, $previousCase->stmts)) {
            return false;
        }

        foreach ($currentCase->stmts as $stmt) {
            if ($stmt instanceof Break_) {
                return true;
            }

            if ($stmt instanceof Return_) {
                return true;
            }
        }

        return false;
    }
}
