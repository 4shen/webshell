<?php

declare(strict_types=1);

namespace Rector\Core\Rector\Visibility;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassConst;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Core\Tests\Rector\Visibility\ChangeConstantVisibilityRector\ChangeConstantVisibilityRectorTest
 */
final class ChangeConstantVisibilityRector extends AbstractRector
{
    /**
     * @var string[][] { class => [ method name => visibility ] }
     */
    private $constantToVisibilityByClass = [];

    /**
     * @param string[][] $constantToVisibilityByClass
     */
    public function __construct(array $constantToVisibilityByClass = [])
    {
        $this->constantToVisibilityByClass = $constantToVisibilityByClass;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Change visibility of constant from parent class.',
            [new ConfiguredCodeSample(
                <<<'PHP'
class FrameworkClass
{
    protected const SOME_CONSTANT = 1;
}

class MyClass extends FrameworkClass
{
    public const SOME_CONSTANT = 1;
}
PHP
                ,
                <<<'PHP'
class FrameworkClass
{
    protected const SOME_CONSTANT = 1;
}

class MyClass extends FrameworkClass
{
    protected const SOME_CONSTANT = 1;
}
PHP
                ,
                [
                    'ParentObject' => [
                        'SOME_CONSTANT' => 'protected',
                    ],
                ]
            )]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassConst::class];
    }

    /**
     * @param ClassConst $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($this->constantToVisibilityByClass as $class => $constantsToVisibility) {
            if (! $this->isObjectType($node, $class)) {
                continue;
            }

            foreach ($constantsToVisibility as $constant => $visibility) {
                if (! $this->isName($node, $constant)) {
                    continue;
                }

                $this->changeNodeVisibility($node, $visibility);

                return $node;
            }
        }

        return null;
    }
}
