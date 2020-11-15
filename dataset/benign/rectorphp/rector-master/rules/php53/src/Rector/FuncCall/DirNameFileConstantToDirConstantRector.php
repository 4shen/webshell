<?php

declare(strict_types=1);

namespace Rector\Php53\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\MagicConst\Dir;
use PhpParser\Node\Scalar\MagicConst\File;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\ValueObject\PhpVersionFeature;

/**
 * @see \Rector\Php53\Tests\Rector\FuncCall\DirNameFileConstantToDirConstantRector\DirNameFileConstantToDirConstantRectorTest
 */
final class DirNameFileConstantToDirConstantRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Convert dirname(__FILE__) to __DIR__', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        return dirname(__FILE__);
    }
}
PHP
                ,
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        return __DIR__;
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
        if (! $this->isAtLeastPhpVersion(PhpVersionFeature::DIR_CONSTANT)) {
            return null;
        }

        if (! $this->isName($node, 'dirname')) {
            return null;
        }

        if (count($node->args) !== 1) {
            return null;
        }

        $firstArgValue = $node->args[0]->value;
        if (! $firstArgValue instanceof File) {
            return null;
        }

        return new Dir();
    }
}
