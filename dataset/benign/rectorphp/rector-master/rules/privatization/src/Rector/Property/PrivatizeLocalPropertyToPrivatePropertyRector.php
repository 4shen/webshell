<?php

declare(strict_types=1);

namespace Rector\Privatization\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Property;
use PHPUnit\Framework\TestCase;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeCollector\NodeFinder\PropertyFetchParsedNodesFinder;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\VendorLocker\NodeVendorLocker\PropertyVisibilityVendorLockResolver;

/**
 * @see \Rector\Privatization\Tests\Rector\Property\PrivatizeLocalPropertyToPrivatePropertyRector\PrivatizeLocalPropertyToPrivatePropertyRectorTest
 */
final class PrivatizeLocalPropertyToPrivatePropertyRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private const ANNOTATIONS_REQUIRING_PUBLIC = [
        'api',
        // Symfony DI
        'required',
        // other DI
        'inject',
    ];

    /**
     * @var PropertyFetchParsedNodesFinder
     */
    private $propertyFetchParsedNodesFinder;

    /**
     * @var PropertyVisibilityVendorLockResolver
     */
    private $propertyVisibilityVendorLockResolver;

    public function __construct(
        PropertyFetchParsedNodesFinder $propertyFetchParsedNodesFinder,
        PropertyVisibilityVendorLockResolver $propertyVisibilityVendorLockResolver
    ) {
        $this->propertyFetchParsedNodesFinder = $propertyFetchParsedNodesFinder;
        $this->propertyVisibilityVendorLockResolver = $propertyVisibilityVendorLockResolver;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Privatize local-only property to private property', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public $value;

    public function run()
    {
        return $this->value;
    }
}
PHP
,
                <<<'PHP'
class SomeClass
{
    private $value;

    public function run()
    {
        return $this->value;
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
        if ($this->shouldSkip($node)) {
            return null;
        }

        $propertyFetches = $this->propertyFetchParsedNodesFinder->findPropertyFetchesByProperty($node);

        $usedPropertyFetchClassNames = [];
        foreach ($propertyFetches as $propertyFetch) {
            $usedPropertyFetchClassNames[] = $propertyFetch->getAttribute(AttributeKey::CLASS_NAME);
        }

        $usedPropertyFetchClassNames = array_unique($usedPropertyFetchClassNames);

        $propertyClassName = $node->getAttribute(AttributeKey::CLASS_NAME);

        // has external usage
        if ([$propertyClassName] !== $usedPropertyFetchClassNames) {
            return null;
        }

        $this->makePrivate($node);

        return $node;
    }

    private function shouldSkip(Property $property): bool
    {
        $classNode = $property->getAttribute(AttributeKey::CLASS_NODE);
        if ($this->shouldSkipClass($classNode)) {
            return true;
        }

        if ($this->shouldSkipProperty($property)) {
            return true;
        }

        // is parent required property? skip it
        if ($this->propertyVisibilityVendorLockResolver->isParentLockedProperty($property)) {
            return true;
        }

        if ($this->propertyVisibilityVendorLockResolver->isChildLockedProperty($property)) {
            return true;
        }

        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $property->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return false;
        }

        foreach (self::ANNOTATIONS_REQUIRING_PUBLIC as $annotationRequiringPublic) {
            if ($phpDocInfo->hasByName($annotationRequiringPublic)) {
                return true;
            }
        }

        return false;
    }

    private function shouldSkipClass(?ClassLike $classLike): bool
    {
        if (! $classLike instanceof Class_) {
            return true;
        }

        if ($this->isAnonymousClass($classLike)) {
            return true;
        }

        if ($this->isObjectType($classLike, TestCase::class)) {
            return true;
        }

        return $this->isObjectType($classLike, 'PHP_CodeSniffer\Sniffs\Sniff');
    }

    private function shouldSkipProperty(Property $property): bool
    {
        // already private
        if ($property->isPrivate()) {
            return true;
        }

        // skip for now
        return $property->isStatic();
    }
}
