<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeTraverser;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\Manipulator\ClassMethodPropertyFetchManipulator;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PHPStan\Type\AliasedObjectType;
use Rector\PHPStan\Type\FullyQualifiedObjectType;
use Rector\TypeDeclaration\Contract\TypeInferer\PropertyTypeInfererInterface;
use Rector\TypeDeclaration\TypeInferer\AbstractTypeInferer;

final class ConstructorPropertyTypeInferer extends AbstractTypeInferer implements PropertyTypeInfererInterface
{
    /**
     * @var ClassMethodPropertyFetchManipulator
     */
    private $classMethodPropertyFetchManipulator;

    public function __construct(ClassMethodPropertyFetchManipulator $classMethodPropertyFetchManipulator)
    {
        $this->classMethodPropertyFetchManipulator = $classMethodPropertyFetchManipulator;
    }

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
        if (! is_string($propertyName)) {
            throw new ShouldNotHappenException();
        }

        $param = $this->classMethodPropertyFetchManipulator->resolveParamForPropertyFetch($classMethod, $propertyName);
        if ($param === null) {
            return new MixedType();
        }

        // A. infer from type declaration of parameter
        if ($param->type !== null) {
            return $this->resolveFromParamType($param, $classMethod, $propertyName);
        }

        return new MixedType();
    }

    public function getPriority(): int
    {
        return 800;
    }

    private function resolveFromParamType(Param $param, ClassMethod $classMethod, string $propertyName): Type
    {
        $type = $this->resolveParamTypeToPHPStanType($param);
        if ($type instanceof MixedType) {
            return new MixedType();
        }

        $types = [];

        // it's an array - annotation → make type more precise, if possible
        if ($type instanceof ArrayType) {
            $types[] = $this->getResolveParamStaticTypeAsPHPStanType($classMethod, $propertyName);
        } else {
            $types[] = $type;
        }

        if ($this->isParamNullable($param)) {
            $types[] = new NullType();
        }

        return $this->typeFactory->createMixedPassedOrUnionType($types);
    }

    private function resolveFullyQualifiedOrAliasedObjectType(Param $param): ?Type
    {
        if ($param->type === null) {
            return null;
        }

        $fullyQualifiedName = $this->nodeNameResolver->getName($param->type);
        if (! $fullyQualifiedName) {
            return null;
        }

        $originalName = $param->type->getAttribute(AttributeKey::ORIGINAL_NAME);
        if (! $originalName instanceof Name) {
            return null;
        }

        // if the FQN has different ending than the original, it was aliased and we need to return the alias
        if (! Strings::endsWith($fullyQualifiedName, '\\' . $originalName->toString())) {
            $className = $originalName->toString();

            if (class_exists($className)) {
                return new FullyQualifiedObjectType($className);
            }

            // @note: $fullyQualifiedName is a guess, needs real life test
            return new AliasedObjectType($originalName->toString(), $fullyQualifiedName);
        }

        return null;
    }

    private function resolveParamTypeToPHPStanType(Param $param): Type
    {
        if ($param->type === null) {
            return new MixedType();
        }

        if ($param->type instanceof NullableType) {
            $types = [];
            $types[] = new NullType();
            $types[] = $this->staticTypeMapper->mapPhpParserNodePHPStanType($param->type->type);

            return $this->typeFactory->createMixedPassedOrUnionType($types);
        }

        // special case for alias
        if ($param->type instanceof FullyQualified) {
            $type = $this->resolveFullyQualifiedOrAliasedObjectType($param);
            if ($type !== null) {
                return $type;
            }
        }

        return $this->staticTypeMapper->mapPhpParserNodePHPStanType($param->type);
    }

    private function getResolveParamStaticTypeAsPHPStanType(ClassMethod $classMethod, string $propertyName): Type
    {
        $paramStaticType = new ArrayType(new MixedType(), new MixedType());

        $this->callableNodeTraverser->traverseNodesWithCallable((array) $classMethod->stmts, function (Node $node) use (
            $propertyName,
            &$paramStaticType
        ): ?int {
            if (! $node instanceof Variable) {
                return null;
            }

            if (! $this->nodeNameResolver->isName($node, $propertyName)) {
                return null;
            }

            $paramStaticType = $this->nodeTypeResolver->getStaticType($node);

            return NodeTraverser::STOP_TRAVERSAL;
        });

        return $paramStaticType;
    }

    private function isParamNullable(Param $param): bool
    {
        if ($param->type instanceof NullableType) {
            return true;
        }

        if ($param->default !== null) {
            $defaultValueStaticType = $this->nodeTypeResolver->getStaticType($param->default);
            if ($defaultValueStaticType instanceof NullType) {
                return true;
            }
        }

        return false;
    }
}
