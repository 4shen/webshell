<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\AnnotationReader;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\Reader;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\DoctrineAnnotationGenerated\PhpDocNode\ConstantReferenceIdentifierRestorer;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\ClassExistenceStaticHelper;
use Rector\NodeTypeResolver\Node\AttributeKey;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use Throwable;

final class NodeAnnotationReader
{
    /**
     * @var string[]
     */
    private $alreadyProvidedAnnotations = [];

    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var ConstantReferenceIdentifierRestorer
     */
    private $constantReferenceIdentifierRestorer;

    public function __construct(
        Reader $reader,
        NodeNameResolver $nodeNameResolver,
        ConstantReferenceIdentifierRestorer $constantReferenceIdentifierRestorer
    ) {
        $this->reader = $reader;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->constantReferenceIdentifierRestorer = $constantReferenceIdentifierRestorer;
    }

    /**
     * @return object|null
     */
    public function readAnnotation(Node $node, string $annotationClass)
    {
        if ($node instanceof Property) {
            return $this->readPropertyAnnotation($node, $annotationClass);
        }

        if ($node instanceof ClassMethod) {
            return $this->readMethodAnnotation($node, $annotationClass);
        }

        if ($node instanceof Class_) {
            return $this->readClassAnnotation($node, $annotationClass);
        }

        return null;
    }

    /**
     * @return object|null
     */
    public function readClassAnnotation(Class_ $class, string $annotationClassName)
    {
        $classReflection = $this->createClassReflectionFromNode($class);

        $annotation = $this->reader->getClassAnnotation($classReflection, $annotationClassName);
        if ($annotation === null) {
            return null;
        }

        $this->constantReferenceIdentifierRestorer->restoreObject($annotation);

        return $annotation;
    }

    /**
     * @return object|null
     */
    public function readPropertyAnnotation(Property $property, string $annotationClassName)
    {
        $propertyReflection = $this->createPropertyReflectionFromPropertyNode($property);
        if ($propertyReflection === null) {
            return null;
        }

        try {
            // covers cases like https://github.com/rectorphp/rector/issues/3046

            /** @var object[] $propertyAnnotations */
            $propertyAnnotations = $this->reader->getPropertyAnnotations($propertyReflection);

            foreach ($propertyAnnotations as $propertyAnnotation) {
                if (! is_a($propertyAnnotation, $annotationClassName, true)) {
                    continue;
                }

                $objectHash = md5(spl_object_hash($propertyReflection) . serialize($propertyAnnotation));
                if (in_array($objectHash, $this->alreadyProvidedAnnotations, true)) {
                    continue;
                }

                $this->alreadyProvidedAnnotations[] = $objectHash;
                $this->constantReferenceIdentifierRestorer->restoreObject($propertyAnnotation);

                return $propertyAnnotation;
            }
        } catch (AnnotationException $annotationException) {
            // unable to laod
            return null;
        }

        return null;
    }

    /**
     * @return object|null
     */
    private function readMethodAnnotation(ClassMethod $classMethod, string $annotationClassName)
    {
        /** @var string $className */
        $className = $classMethod->getAttribute(AttributeKey::CLASS_NAME);

        /** @var string $methodName */
        $methodName = $this->nodeNameResolver->getName($classMethod);

        $reflectionMethod = new ReflectionMethod($className, $methodName);

        try {
            // covers cases like https://github.com/rectorphp/rector/issues/3046

            /** @var object[] $methodAnnotations */
            $methodAnnotations = $this->reader->getMethodAnnotations($reflectionMethod);
            foreach ($methodAnnotations as $methodAnnotation) {
                if (! is_a($methodAnnotation, $annotationClassName, true)) {
                    continue;
                }

                $objectHash = md5(spl_object_hash($classMethod) . serialize($methodAnnotation));
                if (in_array($objectHash, $this->alreadyProvidedAnnotations, true)) {
                    continue;
                }

                $this->alreadyProvidedAnnotations[] = $objectHash;
                $this->constantReferenceIdentifierRestorer->restoreObject($methodAnnotation);

                return $methodAnnotation;
            }
        } catch (AnnotationException $annotationException) {
            // unable to load
            return null;
        }

        return null;
    }

    private function createClassReflectionFromNode(Class_ $class): ReflectionClass
    {
        /** @var string $className */
        $className = $this->nodeNameResolver->getName($class);

        return new ReflectionClass($className);
    }

    private function createPropertyReflectionFromPropertyNode(Property $property): ?ReflectionProperty
    {
        /** @var string $propertyName */
        $propertyName = $this->nodeNameResolver->getName($property);

        /** @var string|null $className */
        $className = $property->getAttribute(AttributeKey::CLASS_NAME);

        if ($className === null || ! ClassExistenceStaticHelper::doesClassLikeExist($className)) {
            // probably fresh node
            return null;
        }

        try {
            return new ReflectionProperty($className, $propertyName);
        } catch (Throwable $throwable) {
            // in case of PHPUnit property or just-added property
            return null;
        }
    }
}
