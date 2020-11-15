<?php

declare(strict_types=1);

namespace Rector\Celebrity\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Cast\Array_;
use PhpParser\Node\Expr\Cast\Bool_;
use PhpParser\Node\Expr\Cast\Double;
use PhpParser\Node\Expr\Cast\Int_;
use PhpParser\Node\Expr\Cast\Object_;
use PhpParser\Node\Expr\Cast\String_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see https://stackoverflow.com/questions/5577003/using-settype-in-php-instead-of-typecasting-using-brackets-what-is-the-differen/5577068#5577068
 * @see https://github.com/FriendsOfPHP/PHP-CS-Fixer/pull/3709
 * @see \Rector\Celebrity\Tests\Rector\FuncCall\SetTypeToCastRector\SetTypeToCastRectorTest
 */
final class SetTypeToCastRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private const TYPE_TO_CAST = [
        'array' => Array_::class,
        'bool' => Bool_::class,
        'boolean' => Bool_::class,
        'double' => Double::class,
        'float' => Double::class,
        'int' => Int_::class,
        'integer' => Int_::class,
        'object' => Object_::class,
        'string' => String_::class,
    ];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Changes settype() to (type) where possible', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run($foo)
    {
        settype($foo, 'string');

        return settype($foo, 'integer');
    }
}
PHP
                ,
                <<<'PHP'
class SomeClass
{
    public function run(array $items)
    {
        $foo = (string) $foo;

        return (int) $foo;
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
        if (! $this->isName($node, 'settype')) {
            return null;
        }

        $typeNode = $this->getValue($node->args[1]->value);
        if ($typeNode === null) {
            return null;
        }

        $typeNode = strtolower($typeNode);

        $varNode = $node->args[0]->value;
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);

        // result of function or probably used
        if ($parentNode instanceof Expr || $parentNode instanceof Arg) {
            return null;
        }

        if (isset(self::TYPE_TO_CAST[$typeNode])) {
            $castClass = self::TYPE_TO_CAST[$typeNode];
            $castNode = new $castClass($varNode);

            if ($parentNode instanceof Expression) {
                // bare expression? → assign
                return new Assign($varNode, $castNode);
            }

            return $castNode;
        }

        if ($typeNode === 'null') {
            return new Assign($varNode, $this->createNull());
        }

        return $node;
    }
}
