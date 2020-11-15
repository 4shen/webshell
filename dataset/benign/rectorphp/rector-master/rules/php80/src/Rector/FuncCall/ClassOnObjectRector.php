<?php

declare(strict_types=1);

namespace Rector\Php80\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\FuncCall;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\ValueObject\PhpVersionFeature;

/**
 * @see https://wiki.php.net/rfc/class_name_literal_on_object
 *
 * @see \Rector\Php80\Tests\Rector\FuncCall\ClassOnObjectRector\ClassOnObjectRectorTest
 */
final class ClassOnObjectRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change get_class($object) to faster $object::class', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run($object)
    {
        return get_class($object);
    }
}
PHP
,
                <<<'PHP'
class SomeClass
{
    public function run($object)
    {
        return $object::class;
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
        if (! $this->isAtLeastPhpVersion(PhpVersionFeature::CLASS_ON_OBJECT)) {
            return null;
        }

        if (! $this->isFuncCallName($node, 'get_class')) {
            return null;
        }

        $object = $node->args[0]->value;

        return new ClassConstFetch($object, 'class');
    }
}
