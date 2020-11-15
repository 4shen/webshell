<?php

declare(strict_types=1);

namespace Rector\Core\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name\FullyQualified;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Core\Tests\Rector\FuncCall\FunctionToNewRector\FunctionToNewRectorTest
 */
final class FunctionToNewRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $functionToNew = [];

    /**
     * @param string[] $functionToNew
     */
    public function __construct(array $functionToNew = [])
    {
        $this->functionToNew = $functionToNew;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change configured function calls to new Instance', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        $array = collection([]);
    }
}
PHP
                ,
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        $array = new \Collection([]);
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
        foreach ($this->functionToNew as $function => $new) {
            if (! $this->isName($node, $function)) {
                continue;
            }

            return new New_(new FullyQualified($new), $node->args);
        }

        return null;
    }
}
