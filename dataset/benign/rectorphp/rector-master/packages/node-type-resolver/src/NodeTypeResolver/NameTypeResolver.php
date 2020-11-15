<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\NameTypeResolver\NameTypeResolverTest
 */
final class NameTypeResolver implements NodeTypeResolverInterface
{
    /**
     * @return string[]
     */
    public function getNodeClasses(): array
    {
        return [Name::class, FullyQualified::class];
    }

    /**
     * @param Name $nameNode
     */
    public function resolve(Node $nameNode): Type
    {
        $name = $nameNode->toString();

        if ($name === 'parent') {
            return $this->resolveParent($nameNode);
        }

        $fullyQualifiedName = $this->resolveFullyQualifiedName($nameNode, $name);
        if ($fullyQualifiedName === null) {
            return new MixedType();
        }

        return new ObjectType($fullyQualifiedName);
    }

    /**
     * @return ObjectType|UnionType|MixedType
     */
    private function resolveParent(Name $name): Type
    {
        /** @var string|null $parentClassName */
        $parentClassName = $name->getAttribute(AttributeKey::PARENT_CLASS_NAME);

        // missing parent class, probably unused parent:: call
        if ($parentClassName === null) {
            return new MixedType();
        }

        $type = new ObjectType($parentClassName);

        $parentParentClass = get_parent_class($parentClassName);
        if ($parentParentClass) {
            $type = new UnionType([$type, new ObjectType($parentParentClass)]);
        }

        return $type;
    }

    private function resolveFullyQualifiedName(Node $nameNode, string $name): string
    {
        if (in_array($name, ['self', 'static', 'this'], true)) {
            /** @var string|null $class */
            $class = $nameNode->getAttribute(AttributeKey::CLASS_NAME);
            if ($class === null) {
                // anonymous class probably
                return 'Anonymous';
            }

            return $class;
        }

        /** @var Name|null $resolvedNameNode */
        $resolvedNameNode = $nameNode->getAttribute(AttributeKey::RESOLVED_NAME);
        if ($resolvedNameNode instanceof Name) {
            return $resolvedNameNode->toString();
        }

        return $name;
    }
}
