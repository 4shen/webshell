<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\Function_;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Function_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\Util\StaticRectorStrings;

/**
 * @see \Rector\CodingStyle\Tests\Rector\Function_\CamelCaseFunctionNamingToUnderscoreRector\CamelCaseFunctionNamingToUnderscoreRectorTest
 */
final class CamelCaseFunctionNamingToUnderscoreRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change CamelCase naming of functions to under_score naming', [
            new CodeSample(
                <<<'PHP'
function someCamelCaseFunction()
{
}

someCamelCaseFunction();
PHP
,
                <<<'PHP'
function some_camel_case_function()
{
}

some_camel_case_function();
PHP

            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Function_::class, FuncCall::class];
    }

    /**
     * @param Function_|FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        $shortName = $this->resolveShortName($node);
        if ($shortName === null) {
            return null;
        }

        $underscoredName = StaticRectorStrings::camelCaseToUnderscore($shortName);
        if ($underscoredName === $shortName) {
            return null;
        }

        if ($node instanceof FuncCall) {
            $node->name = new Name($underscoredName);
        } elseif ($node instanceof Function_) {
            $node->name = new Identifier($underscoredName);
        }

        return $node;
    }

    /**
     * @param Function_|FuncCall $node
     */
    private function resolveShortName(Node $node): ?string
    {
        $functionOrFuncCallName = $this->getName($node);
        if ($functionOrFuncCallName === null) {
            return null;
        }

        $shortName = Strings::after($functionOrFuncCallName, '\\', -1);
        if ($shortName === null) {
            return $functionOrFuncCallName;
        }

        return $shortName;
    }
}
