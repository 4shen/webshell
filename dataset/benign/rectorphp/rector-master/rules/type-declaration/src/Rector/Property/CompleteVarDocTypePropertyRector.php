<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\MixedType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer;

/**
 * @see \Rector\TypeDeclaration\Tests\Rector\Property\CompleteVarDocTypePropertyRector\CompleteVarDocTypePropertyRectorTest
 */
final class CompleteVarDocTypePropertyRector extends AbstractRector
{
    /**
     * @var PropertyTypeInferer
     */
    private $propertyTypeInferer;

    public function __construct(PropertyTypeInferer $propertyTypeInferer)
    {
        $this->propertyTypeInferer = $propertyTypeInferer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Complete property `@var` annotations or correct the old ones', [
            new CodeSample(
                <<<'PHP'
final class SomeClass
{
    private $eventDispatcher;

    public function __construct(EventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }
}
PHP
                ,
                <<<'PHP'
final class SomeClass
{
    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    public function __construct(EventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
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
        return [Property::class];
    }

    /**
     * @param Property $node
     */
    public function refactor(Node $node): ?Node
    {
        $propertyType = $this->propertyTypeInferer->inferProperty($node);
        if ($propertyType instanceof MixedType) {
            return null;
        }

        /** @var PhpDocInfo $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
        $phpDocInfo->changeVarType($propertyType);

        return $node;
    }
}
