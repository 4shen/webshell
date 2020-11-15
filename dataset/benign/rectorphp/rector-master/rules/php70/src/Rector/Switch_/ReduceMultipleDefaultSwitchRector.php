<?php

declare(strict_types=1);

namespace Rector\Php70\Rector\Switch_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Case_;
use PhpParser\Node\Stmt\Switch_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see https://3v4l.org/iGDVW
 * @see https://wiki.php.net/rfc/switch.default.multiple
 * @see https://stackoverflow.com/a/44000794/1348344
 * @see https://github.com/franzliedke/wp-mpdf/commit/9dc489215fbd1adcb514810653a73dea71db8e99#diff-2f1f4a51a2dd3a73ca034a48a67a2320L1373
 * @see \Rector\Php70\Tests\Rector\Switch_\ReduceMultipleDefaultSwitchRector\ReduceMultipleDefaultSwitchRectorTest
 */
final class ReduceMultipleDefaultSwitchRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove first default switch, that is ignored', [
            new CodeSample(
                <<<'PHP'
switch ($expr) {
    default:
         echo "Hello World";

    default:
         echo "Goodbye Moon!";
         break;
}
PHP
                ,
                <<<'PHP'
switch ($expr) {
    default:
         echo "Goodbye Moon!";
         break;
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
        $defaultCases = [];
        foreach ($node->cases as $case) {
            if ($case->cond !== null) {
                continue;
            }

            $defaultCases[] = $case;
        }

        if (count($defaultCases) < 2) {
            return null;
        }

        $this->removeExtraDefaultCases($defaultCases);

        return $node;
    }

    /**
     * @param Case_[] $defaultCases
     */
    private function removeExtraDefaultCases(array $defaultCases): void
    {
        // keep only last
        array_pop($defaultCases);
        foreach ($defaultCases as $defaultCase) {
            $this->keepStatementsToParentCase($defaultCase);
            $this->removeNode($defaultCase);
        }
    }

    private function keepStatementsToParentCase(Case_ $caseNode): void
    {
        $previousNode = $caseNode->getAttribute(AttributeKey::PREVIOUS_NODE);
        if (! $previousNode instanceof Case_) {
            return;
        }

        if ($previousNode->stmts === []) {
            $previousNode->stmts = $caseNode->stmts;
            $caseNode->stmts = [];
        }
    }
}
