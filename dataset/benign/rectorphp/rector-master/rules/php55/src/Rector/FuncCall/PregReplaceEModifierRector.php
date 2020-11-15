<?php

declare(strict_types=1);

namespace Rector\Php55\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Php55\NodeFactory\AnonymousFunctionNodeFactory;
use Rector\Php55\RegexMatcher;

/**
 * @see https://wiki.php.net/rfc/remove_preg_replace_eval_modifier
 * @see https://stackoverflow.com/q/19245205/1348344
 *
 * @see \Rector\Php55\Tests\Rector\FuncCall\PregReplaceEModifierRector\PregReplaceEModifierRectorTest
 */
final class PregReplaceEModifierRector extends AbstractRector
{
    /**
     * @var RegexMatcher
     */
    private $regexMatcher;

    /**
     * @var AnonymousFunctionNodeFactory
     */
    private $anonymousFunctionNodeFactory;

    public function __construct(
        RegexMatcher $regexMatcher,
        AnonymousFunctionNodeFactory $anonymousFunctionNodeFactory
    ) {
        $this->regexMatcher = $regexMatcher;
        $this->anonymousFunctionNodeFactory = $anonymousFunctionNodeFactory;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('The /e modifier is no longer supported, use preg_replace_callback instead', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        $comment = preg_replace('~\b(\w)(\w+)~e', '"$1".strtolower("$2")', $comment);
    }
}
PHP
                ,
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        $comment = preg_replace_callback('~\b(\w)(\w+)~', function ($matches) {
              return($matches[1].strtolower($matches[2]));
        }, , $comment);
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
        if (! $this->isName($node, 'preg_replace')) {
            return null;
        }

        $firstArgumentValue = $node->args[0]->value;
        $patternWithoutE = $this->regexMatcher->resolvePatternExpressionWithoutEIfFound($firstArgumentValue);
        if ($patternWithoutE === null) {
            return null;
        }

        $secondArgumentValue = $node->args[1]->value;
        $anonymousFunction = $this->anonymousFunctionNodeFactory->createAnonymousFunctionFromString(
            $secondArgumentValue
        );
        if ($anonymousFunction === null) {
            return null;
        }

        $node->name = new Name('preg_replace_callback');
        $node->args[0]->value = $patternWithoutE;
        $node->args[1]->value = $anonymousFunction;

        return $node;
    }
}
