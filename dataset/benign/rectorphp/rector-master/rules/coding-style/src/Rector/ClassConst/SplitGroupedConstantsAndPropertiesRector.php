<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\ClassConst;

use PhpParser\Node;
use PhpParser\Node\Const_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\CodingStyle\Tests\Rector\ClassConst\SplitGroupedConstantsAndPropertiesRector\SplitGroupedConstantsAndPropertiesRectorTest
 */
final class SplitGroupedConstantsAndPropertiesRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Separate constant and properties to own lines', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    const HI = true, AHOJ = 'true';

    /**
     * @var string
     */
    public $isIt, $isIsThough;
}
PHP
                ,
                <<<'PHP'
class SomeClass
{
    const HI = true;
    const AHOJ = 'true';

    /**
     * @var string
     */
    public $isIt;

    /**
     * @var string
     */
    public $isIsThough;
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
        return [ClassConst::class, Property::class];
    }

    /**
     * @param ClassConst|Property $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof ClassConst) {
            if (count($node->consts) < 2) {
                return null;
            }

            $allConstants = $node->consts;

            /** @var Const_ $firstConstant */
            $firstConstant = array_shift($allConstants);
            $node->consts = [$firstConstant];

            foreach ($allConstants as $anotherConstant) {
                $nextClassConst = new ClassConst([$anotherConstant], $node->flags, $node->getAttributes());
                $this->addNodeAfterNode($nextClassConst, $node);
            }

            return $node;
        }

        if (count($node->props) < 2) {
            return null;
        }

        $allProperties = $node->props;
        /** @var PropertyProperty $firstProperty */
        $firstProperty = array_shift($allProperties);
        $node->props = [$firstProperty];

        foreach ($allProperties as $anotherProperty) {
            $nextProperty = new Property($node->flags, [$anotherProperty], $node->getAttributes());
            $this->addNodeAfterNode($nextProperty, $node);
        }

        return $node;
    }
}
