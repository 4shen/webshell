<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\PhpParser\Node\Manipulator\ClassMethodManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\DeadCode\Tests\Rector\ClassMethod\RemoveEmptyClassMethodRector\RemoveEmptyClassMethodRectorTest
 */
final class RemoveEmptyClassMethodRector extends AbstractRector
{
    /**
     * @var ClassMethodManipulator
     */
    private $classMethodManipulator;

    public function __construct(ClassMethodManipulator $classMethodManipulator)
    {
        $this->classMethodManipulator = $classMethodManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove empty method calls not required by parents', [
            new CodeSample(
                <<<'PHP'
class OrphanClass
{
    public function __construct()
    {
    }
}
PHP
                ,
                <<<'PHP'
class OrphanClass
{
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
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        $classNode = $node->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classNode instanceof Class_) {
            return null;
        }

        if ($node->stmts !== null && $node->stmts !== []) {
            return null;
        }

        if ($node->isAbstract()) {
            return null;
        }

        if (! $classNode->isFinal() && ($node->isProtected() || $node->isPublic()) && ! $this->isName($node, '__*')) {
            return null;
        }

        if ($this->classMethodManipulator->isNamedConstructor($node)) {
            return null;
        }

        if ($this->classMethodManipulator->hasParentMethodOrInterfaceMethod($node)) {
            return null;
        }

        $this->removeNode($node);

        return $node;
    }
}
