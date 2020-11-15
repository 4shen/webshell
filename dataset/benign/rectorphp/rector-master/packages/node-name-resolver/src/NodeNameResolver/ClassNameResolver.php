<?php

declare(strict_types=1);

namespace Rector\NodeNameResolver\NodeNameResolver;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\NodeNameResolver\Contract\NodeNameResolverInterface;
use Rector\NodeNameResolver\NodeNameResolver;

final class ClassNameResolver implements NodeNameResolverInterface
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @required
     */
    public function autowireClassNameResolver(NodeNameResolver $nodeNameResolver): void
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }

    public function getNode(): string
    {
        return Class_::class;
    }

    /**
     * @param Class_ $node
     */
    public function resolve(Node $node): ?string
    {
        if (isset($node->namespacedName)) {
            return $node->namespacedName->toString();
        }

        if ($node->name === null) {
            return null;
        }

        return $this->nodeNameResolver->getName($node->name);
    }
}
