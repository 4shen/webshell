<?php

declare(strict_types=1);

namespace Rector\Core\Rector\ClassLike;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Trait_;
use Rector\Core\PhpParser\Node\Manipulator\ClassManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\Core\Tests\Rector\ClassLike\RemoveTraitRector\RemoveTraitRectorTest
 */
final class RemoveTraitRector extends AbstractRector
{
    /**
     * @var bool
     */
    private $classHasChanged = false;

    /**
     * @var string[]
     */
    private $traitsToRemove = [];

    /**
     * @var ClassManipulator
     */
    private $classManipulator;

    /**
     * @param string[] $traitsToRemove
     */
    public function __construct(ClassManipulator $classManipulator, array $traitsToRemove = [])
    {
        $this->classManipulator = $classManipulator;
        $this->traitsToRemove = $traitsToRemove;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove specific traits from code', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    use SomeTrait;
}
PHP
                ,
                <<<'PHP'
class SomeClass
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
        return [Class_::class, Trait_::class];
    }

    /**
     * @param Class_|Trait_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $usedTraits = $this->classManipulator->getUsedTraits($node);
        if ($usedTraits === []) {
            return null;
        }

        $this->classHasChanged = false;
        $this->removeTraits($usedTraits);

        // invoke re-print
        if ($this->classHasChanged) {
            $node->setAttribute(AttributeKey::ORIGINAL_NODE, null);
        }

        return $node;
    }

    /**
     * @param Name[] $usedTraits
     */
    private function removeTraits(array $usedTraits): void
    {
        foreach ($usedTraits as $usedTrait) {
            foreach ($this->traitsToRemove as $traitToRemove) {
                if ($this->isName($usedTrait, $traitToRemove)) {
                    $this->removeNode($usedTrait);
                    $this->classHasChanged = true;
                    continue 2;
                }
            }
        }
    }
}
