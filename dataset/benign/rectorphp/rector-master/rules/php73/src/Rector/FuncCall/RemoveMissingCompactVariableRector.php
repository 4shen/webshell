<?php

declare(strict_types=1);

namespace Rector\Php73\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see https://3v4l.org/bZ61T
 * @see \Rector\Php73\Tests\Rector\FuncCall\RemoveMissingCompactVariableRector\RemoveMissingCompactVariableRectorTest
 */
final class RemoveMissingCompactVariableRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove non-existing vars from compact()', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        $value = 'yes';

        compact('value', 'non_existing');
    }
}
PHP
                ,
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        $value = 'yes';

        compact('value');
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
        return [FuncCall::class];
    }

    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isName($node, 'compact')) {
            return null;
        }

        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            return null;
        }

        foreach ($node->args as $key => $arg) {
            $argValue = $this->getValue($arg->value);

            if (! $scope->hasVariableType($argValue)->no()) {
                continue;
            }

            unset($node->args[$key]);
        }

        if ($node->args === []) {
            return new Array_();
        }

        return $node;
    }
}
