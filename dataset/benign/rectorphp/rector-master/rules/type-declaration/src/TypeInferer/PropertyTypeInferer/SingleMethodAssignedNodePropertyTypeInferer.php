<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeTraverser;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\TypeDeclaration\Contract\TypeInferer\PropertyTypeInfererInterface;
use Rector\TypeDeclaration\TypeInferer\AbstractTypeInferer;

final class SingleMethodAssignedNodePropertyTypeInferer extends AbstractTypeInferer implements PropertyTypeInfererInterface
{
    public function inferProperty(Property $property): Type
    {
        /** @var Class_|null $class */
        $class = $property->getAttribute(AttributeKey::CLASS_NODE);
        if ($class === null) {
            // anonymous class
            return new MixedType();
        }

        $classMethod = $class->getMethod('__construct');
        if ($classMethod === null) {
            return new MixedType();
        }

        $propertyName = $this->nodeNameResolver->getName($property);

        $assignedNode = $this->resolveAssignedNodeToProperty($classMethod, $propertyName);
        if ($assignedNode === null) {
            return new MixedType();
        }

        return $this->nodeTypeResolver->getStaticType($assignedNode);
    }

    public function getPriority(): int
    {
        return 750;
    }

    private function resolveAssignedNodeToProperty(ClassMethod $classMethod, string $propertyName): ?Expr
    {
        $assignedNode = null;
        $this->callableNodeTraverser->traverseNodesWithCallable((array) $classMethod->stmts, function (Node $node) use (
            $propertyName,
            &$assignedNode
        ): ?int {
            if (! $node instanceof Assign) {
                return null;
            }

            if (! $this->nodeNameResolver->isName($node->var, $propertyName)) {
                return null;
            }

            $assignedNode = $node->expr;

            return NodeTraverser::STOP_TRAVERSAL;
        });

        return $assignedNode;
    }
}
