<?php

declare(strict_types=1);

namespace Rector\DoctrineGedmoToKnplabs\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\PhpParser\Node\Manipulator\ClassManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see https://github.com/Atlantic18/DoctrineExtensions/blob/v2.4.x/doc/timestampable.md
 * @see https://github.com/KnpLabs/DoctrineBehaviors/blob/4e0677379dd4adf84178f662d08454a9627781a8/docs/timestampable.md
 *
 * @see \Rector\DoctrineGedmoToKnplabs\Tests\Rector\Class_\TimestampableBehaviorRector\TimestampableBehaviorRectorTest
 */
final class TimestampableBehaviorRector extends AbstractRector
{
    /**
     * @var ClassManipulator
     */
    private $classManipulator;

    public function __construct(ClassManipulator $classManipulator)
    {
        $this->classManipulator = $classManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Change Timestampable from gedmo/doctrine-extensions to knplabs/doctrine-behaviors',
            [
                new CodeSample(
                    <<<'PHP'
use Gedmo\Timestampable\Traits\TimestampableEntity;

class SomeClass
{
    use TimestampableEntity;
}
PHP
,
                    <<<'PHP'
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;

class SomeClass implements TimestampableInterface
{
    use TimestampableTrait;
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
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->classManipulator->hasTrait($node, 'Gedmo\Timestampable\Traits\TimestampableEntity')) {
            return null;
        }

        $this->classManipulator->replaceTrait(
            $node,
            'Gedmo\Timestampable\Traits\TimestampableEntity',
            'Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait'
        );

        $node->implements[] = new FullyQualified('Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface');

        return $node;
    }
}
