<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Node\Manipulator;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Type\Type;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\NodeTypeResolver\ClassExistenceStaticHelper;
use Rector\NodeTypeResolver\Node\AttributeKey;
use ReflectionClass;
use ReflectionProperty;

final class ClassDependencyManipulator
{
    /**
     * @var string
     */
    private const CONSTRUCTOR = '__construct';

    /**
     * @var ClassMethodAssignManipulator
     */
    private $classMethodAssignManipulator;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var ChildAndParentClassManipulator
     */
    private $childAndParentClassManipulator;

    /**
     * @var StmtsManipulator
     */
    private $stmtsManipulator;

    /**
     * @var ClassInsertManipulator
     */
    private $classInsertManipulator;

    public function __construct(
        ClassMethodAssignManipulator $classMethodAssignManipulator,
        NodeFactory $nodeFactory,
        ChildAndParentClassManipulator $childAndParentClassManipulator,
        StmtsManipulator $stmtsManipulator,
        ClassInsertManipulator $classInsertManipulator
    ) {
        $this->classMethodAssignManipulator = $classMethodAssignManipulator;
        $this->nodeFactory = $nodeFactory;
        $this->childAndParentClassManipulator = $childAndParentClassManipulator;
        $this->stmtsManipulator = $stmtsManipulator;
        $this->classInsertManipulator = $classInsertManipulator;
    }

    public function addConstructorDependency(Class_ $class, string $name, ?Type $type): void
    {
        if ($this->isPropertyAlreadyAvailableInTheClassOrItsParents($class, $name)) {
            return;
        }

        $this->classInsertManipulator->addPropertyToClass($class, $name, $type);

        $propertyAssignNode = $this->nodeFactory->createPropertyAssignment($name);
        $this->addConstructorDependencyWithCustomAssign($class, $name, $type, $propertyAssignNode);
    }

    public function addConstructorDependencyWithCustomAssign(
        Class_ $classNode,
        string $name,
        ?Type $type,
        Assign $assign
    ): void {
        $constructorMethod = $classNode->getMethod(self::CONSTRUCTOR);

        /** @var ClassMethod|null $constructorMethod */
        if ($constructorMethod !== null) {
            $this->classMethodAssignManipulator->addParameterAndAssignToMethod(
                $constructorMethod,
                $name,
                $type,
                $assign
            );
            return;
        }

        $constructorMethod = $this->nodeFactory->createPublicMethod(self::CONSTRUCTOR);

        $this->classMethodAssignManipulator->addParameterAndAssignToMethod($constructorMethod, $name, $type, $assign);

        $this->childAndParentClassManipulator->completeParentConstructor($classNode, $constructorMethod);

        $this->classInsertManipulator->addAsFirstMethod($classNode, $constructorMethod);

        $this->childAndParentClassManipulator->completeChildConstructors($classNode, $constructorMethod);
    }

    /**
     * @param Stmt[] $stmts
     */
    public function addStmtsToConstructorIfNotThereYet(Class_ $class, array $stmts): void
    {
        $classMethod = $class->getMethod(self::CONSTRUCTOR);

        if ($classMethod === null) {
            $classMethod = $this->nodeFactory->createPublicMethod(self::CONSTRUCTOR);

            // keep parent constructor call
            if ($this->hasClassParentClassMethod($class, self::CONSTRUCTOR)) {
                $classMethod->stmts[] = $this->createParentClassMethodCall(self::CONSTRUCTOR);
            }

            $classMethod->stmts = array_merge((array) $classMethod->stmts, $stmts);

            $class->stmts = array_merge((array) $class->stmts, [$classMethod]);
            return;
        }

        $stmts = $this->stmtsManipulator->filterOutExistingStmts($classMethod, $stmts);

        // all stmts are already there → skip
        if ($stmts === []) {
            return;
        }

        $classMethod->stmts = array_merge($stmts, (array) $classMethod->stmts);
    }

    public function addInjectProperty(Class_ $class, string $propertyName, ?Type $propertyType): void
    {
        if ($this->isPropertyAlreadyAvailableInTheClassOrItsParents($class, $propertyName)) {
            return;
        }

        $this->classInsertManipulator->addInjectPropertyToClass($class, $propertyName, $propertyType);
    }

    private function hasClassParentClassMethod(Class_ $class, string $methodName): bool
    {
        $parentClassName = $class->getAttribute(AttributeKey::PARENT_CLASS_NAME);
        if ($parentClassName === null) {
            return false;
        }

        return method_exists($parentClassName, $methodName);
    }

    private function createParentClassMethodCall(string $methodName): Expression
    {
        $staticCall = new StaticCall(new Name('parent'), $methodName);

        return new Expression($staticCall);
    }

    private function isPropertyAlreadyAvailableInTheClassOrItsParents(Class_ $class, string $propertyName): bool
    {
        $className = $class->getAttribute(AttributeKey::CLASS_NAME);
        if ($className === null) {
            return false;
        }

        if (! ClassExistenceStaticHelper::doesClassLikeExist($className)) {
            return false;
        }

        $availablePropertyReflections = $this->getParentClassPublicAndProtectedPropertyReflections($className);

        foreach ($availablePropertyReflections as $availablePropertyReflection) {
            if ($availablePropertyReflection->getName() !== $propertyName) {
                continue;
            }

            return true;
        }

        return false;
    }

    /**
     * @return ReflectionProperty[]
     */
    private function getParentClassPublicAndProtectedPropertyReflections(string $className): array
    {
        $parentClassNames = class_parents($className);

        $propertyReflections = [];

        foreach ($parentClassNames as $parentClassName) {
            $parentClassReflection = new ReflectionClass($parentClassName);

            $currentPropertyReflections = $parentClassReflection->getProperties(
                ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED
            );
            $propertyReflections = array_merge($propertyReflections, $currentPropertyReflections);
        }

        return $propertyReflections;
    }
}
