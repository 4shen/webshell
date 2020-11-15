<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\Cast\Int_;
use PhpParser\Node\Expr\FuncCall;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see https://github.com/kalessil/phpinspectionsea/commit/25f53c8c7e08234c34b0d21f308f7c5cbd7a6c95
 * @see https://www.php.net/manual/en/function.intval.php
 *
 * @see \Rector\CodeQuality\Tests\Rector\FuncCall\IntvalToTypeCastRector\IntvalToTypeCastRectorTest
 */
final class IntvalToTypeCastRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change intval() to faster and readable (int) $value', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run($value)
    {
        return intval($value);
    }
}
PHP
,
                <<<'PHP'
class SomeClass
{
    public function run($value)
    {
        return (int) $value;
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
        if (! $this->isName($node, 'intval')) {
            return null;
        }

        if (isset($node->args[1])) {
            $secondArgumentValue = $this->getValue($node->args[1]->value);
            // default value
            if ($secondArgumentValue !== 10) {
                return null;
            }
        }

        return new Int_($node->args[0]->value);
    }
}
