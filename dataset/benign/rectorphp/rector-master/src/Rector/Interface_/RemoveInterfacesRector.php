<?php

declare(strict_types=1);

namespace Rector\Core\Rector\Interface_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Core\Tests\Rector\Interface_\RemoveInterfacesRector\RemoveInterfacesRectorTest
 */
final class RemoveInterfacesRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $interfacesToRemove = [];

    /**
     * @param string[] $interfacesToRemove
     */
    public function __construct(array $interfacesToRemove = [])
    {
        $this->interfacesToRemove = $interfacesToRemove;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Removes interfaces usage from class.', [
            new ConfiguredCodeSample(
                <<<'PHP'
class SomeClass implements SomeInterface
{
}
PHP
                ,
                <<<'PHP'
class SomeClass
{
}
PHP
                ,
                ['SomeInterface']
            ),
        ]);
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
        if ($node->implements === []) {
            return null;
        }

        foreach ($node->implements as $key => $implement) {
            if ($this->isNames($implement, $this->interfacesToRemove)) {
                unset($node->implements[$key]);
            }
        }

        return $node;
    }
}
