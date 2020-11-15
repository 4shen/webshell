<?php

declare(strict_types=1);

namespace Rector\Php73\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see https://wiki.php.net/rfc/case_insensitive_constant_deprecation
 * @see \Rector\Php73\Tests\Rector\FuncCall\SensitiveDefineRector\SensitiveDefineRectorTest
 */
final class SensitiveDefineRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Changes case insensitive constants to sensitive ones.',
            [new CodeSample(<<<'PHP'
define('FOO', 42, true); 
PHP
                , <<<'PHP'
define('FOO', 42); 
PHP
            )]
        );
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
        if (! $this->isName($node, 'define')) {
            return null;
        }

        if (! isset($node->args[2])) {
            return null;
        }

        unset($node->args[2]);

        return $node;
    }
}
