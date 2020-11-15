<?php

declare(strict_types=1);

namespace Rector\Php74\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Cast\String_;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see https://wiki.php.net/rfc/deprecations_php_7_4 (not confirmed yet)
 * @see https://3v4l.org/RTCUq
 * @see \Rector\Php74\Tests\Rector\StaticCall\ExportToReflectionFunctionRector\ExportToReflectionFunctionRectorTest
 */
final class ExportToReflectionFunctionRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change export() to ReflectionFunction alternatives', [
            new CodeSample(
                <<<'PHP'
$reflectionFunction = ReflectionFunction::export('foo');
$reflectionFunctionAsString = ReflectionFunction::export('foo', true);
PHP
                ,
                <<<'PHP'
$reflectionFunction = new ReflectionFunction('foo');
$reflectionFunctionAsString = (string) new ReflectionFunction('foo');
PHP
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [StaticCall::class];
    }

    /**
     * @param StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $node->class instanceof Name) {
            return null;
        }

        if (! $this->isStaticCallNamed($node, 'ReflectionFunction', 'export')) {
            return null;
        }

        $newNode = new New_($node->class, [new Arg($node->args[0]->value)]);

        if (isset($node->args[1]) && $this->isTrue($node->args[1]->value)) {
            return new String_($newNode);
        }

        return $newNode;
    }
}
