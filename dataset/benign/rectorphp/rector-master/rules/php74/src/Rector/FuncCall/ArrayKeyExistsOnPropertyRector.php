<?php

declare(strict_types=1);

namespace Rector\Php74\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see https://wiki.php.net/rfc/deprecations_php_7_4 (not confirmed yet)
 * @see https://3v4l.org/69mpd
 * @see \Rector\Php74\Tests\Rector\FuncCall\ArrayKeyExistsOnPropertyRector\ArrayKeyExistsOnPropertyRectorTest
 */
final class ArrayKeyExistsOnPropertyRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change array_key_exists() on property to property_exists()', [
            new CodeSample(
                <<<'PHP'
class SomeClass {
     public $value;
}
$someClass = new SomeClass;

array_key_exists('value', $someClass);
PHP
                ,
                <<<'PHP'
class SomeClass {
     public $value;
}
$someClass = new SomeClass;

property_exists($someClass, 'value');
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
        if (! $this->isName($node, 'array_key_exists')) {
            return null;
        }

        if (! $this->getStaticType($node->args[1]->value) instanceof ObjectType) {
            return null;
        }

        $node->name = new Name('property_exists');
        $node->args = array_reverse($node->args);

        return $node;
    }
}
