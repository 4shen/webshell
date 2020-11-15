<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Node;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\UseUse;
use Rector\CodingStyle\ValueObject\NameAndParentValueObject;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class UseManipulator
{
    /**
     * @var NameAndParentValueObject[][]
     */
    private $resolvedNodeNames = [];

    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(BetterNodeFinder $betterNodeFinder, NodeNameResolver $nodeNameResolver)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
    }

    /**
     * @return NameAndParentValueObject[][]
     */
    public function resolveUsedNameNodes(Node $node): array
    {
        $this->resolvedNodeNames = [];

        $this->resolveUsedNames($node);
        $this->resolveUsedClassNames($node);
        $this->resolveTraitUseNames($node);

        return $this->resolvedNodeNames;
    }

    private function resolveUsedNames(Node $node): void
    {
        /** @var Name[] $namedNodes */
        $namedNodes = $this->betterNodeFinder->findInstanceOf($node, Name::class);

        foreach ($namedNodes as $nameNode) {
            /** node name before becoming FQN - attribute from @see NameResolver */
            $originalName = $nameNode->getAttribute(AttributeKey::ORIGINAL_NAME);
            if (! $originalName instanceof Name) {
                continue;
            }

            $parentNode = $nameNode->getAttribute(AttributeKey::PARENT_NODE);
            if ($parentNode === null) {
                throw new ShouldNotHappenException();
            }

            $this->resolvedNodeNames[$originalName->toString()][] = new NameAndParentValueObject(
                $nameNode,
                $parentNode
            );
        }
    }

    private function resolveUsedClassNames(Node $searchNode): void
    {
        /** @var ClassLike[] $classLikes */
        $classLikes = $this->betterNodeFinder->findClassLikes([$searchNode]);

        foreach ($classLikes as $classLike) {
            $classLikeName = $classLike->name;
            if ($classLikeName === null) {
                continue;
            }

            $name = $this->nodeNameResolver->getName($classLikeName);
            if ($name === null) {
                continue;
            }

            $this->resolvedNodeNames[$name][] = new NameAndParentValueObject($classLikeName, $classLike);
        }
    }

    private function resolveTraitUseNames(Node $searchNode): void
    {
        /** @var Identifier[] $identifiers */
        $identifiers = $this->betterNodeFinder->findInstanceOf($searchNode, Identifier::class);

        foreach ($identifiers as $identifier) {
            $parentNode = $identifier->getAttribute(AttributeKey::PARENT_NODE);
            if (! $parentNode instanceof UseUse) {
                continue;
            }

            $this->resolvedNodeNames[$identifier->name][] = new NameAndParentValueObject($identifier, $parentNode);
        }
    }
}
