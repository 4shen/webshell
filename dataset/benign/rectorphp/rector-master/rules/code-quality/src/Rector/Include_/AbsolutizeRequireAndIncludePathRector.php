<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\Include_;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\Include_;
use PhpParser\Node\Scalar\MagicConst\Dir;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see https://github.com/symplify/CodingStandard#includerequire-should-be-followed-by-absolute-path
 *
 * @see \Rector\CodeQuality\Tests\Rector\Include_\AbsolutizeRequireAndIncludePathRector\AbsolutizeRequireAndIncludePathRectorTest
 */
final class AbsolutizeRequireAndIncludePathRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'include/require to absolute path. This Rector might introduce backwards incompatible code, when the include/require beeing changed depends on the current working directory.',
            [
                new CodeSample(
                    <<<'PHP'
class SomeClass
{
    public function run()
    {
        require 'autoload.php';

        require $variable;
    }
}
PHP
,
                    <<<'PHP'
class SomeClass
{
    public function run()
    {
        require __DIR__ . '/autoload.php';

        require $variable;
    }
}
PHP

                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Include_::class];
    }

    /**
     * @param Include_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $node->expr instanceof String_) {
            return null;
        }

        /** @var string $includeValue */
        $includeValue = $this->getValue($node->expr);

        // skip phar
        if (Strings::startsWith($includeValue, 'phar://')) {
            return null;
        }

        // add preslash to string
        // keep dots
        if (! Strings::startsWith($includeValue, '/') && ! Strings::startsWith($includeValue, '.')) {
            $node->expr->value = '/' . $includeValue;
        }

        $node->expr = new Concat(new Dir(), $node->expr);

        return $node;
    }
}
