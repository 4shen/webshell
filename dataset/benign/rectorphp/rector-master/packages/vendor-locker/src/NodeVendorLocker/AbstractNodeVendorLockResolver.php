<?php

declare(strict_types=1);

namespace Rector\VendorLocker\NodeVendorLocker;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Interface_;
use Rector\Core\PhpParser\Node\Manipulator\ClassManipulator;
use Rector\Core\Reflection\StaticRelationsHelper;
use Rector\NodeCollector\NodeCollector\ParsedNodeCollector;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;

abstract class AbstractNodeVendorLockResolver
{
    /**
     * @var ParsedNodeCollector
     */
    protected $parsedNodeCollector;

    /**
     * @var ClassManipulator
     */
    protected $classManipulator;

    /**
     * @var NodeNameResolver
     */
    protected $nodeNameResolver;

    /**
     * @required
     */
    public function autowireAbstractNodeVendorLockResolver(
        ParsedNodeCollector $parsedNodeCollector,
        ClassManipulator $classManipulator,
        NodeNameResolver $nodeNameResolver
    ): void {
        $this->parsedNodeCollector = $parsedNodeCollector;
        $this->classManipulator = $classManipulator;
        $this->nodeNameResolver = $nodeNameResolver;
    }

    protected function hasParentClassChildrenClassesOrImplementsInterface(ClassLike $classLike): bool
    {
        if (($classLike instanceof Class_ || $classLike instanceof Interface_) && $classLike->extends) {
            return true;
        }

        if ($classLike instanceof Class_) {
            if ((bool) $classLike->implements) {
                return true;
            }

            /** @var Class_ $classLike */
            return $this->getChildrenClassesByClass($classLike) !== [];
        }

        return false;
    }

    /**
     * @param Class_|Interface_ $classLike
     */
    protected function isMethodVendorLockedByInterface(ClassLike $classLike, string $methodName): bool
    {
        $interfaceNames = $this->classManipulator->getClassLikeNodeParentInterfaceNames($classLike);

        foreach ($interfaceNames as $interfaceName) {
            if (! $this->hasInterfaceMethod($methodName, $interfaceName)) {
                continue;
            }

            return true;
        }

        return false;
    }

    /**
     * @return string[]
     */
    protected function getChildrenClassesByClass(Class_ $class): array
    {
        $desiredClassName = $class->getAttribute(AttributeKey::CLASS_NAME);
        if ($desiredClassName === null) {
            return [];
        }

        return StaticRelationsHelper::getChildrenOfClass($desiredClassName);
    }

    private function hasInterfaceMethod(string $methodName, string $interfaceName): bool
    {
        if (! interface_exists($interfaceName)) {
            return false;
        }

        $interfaceMethods = get_class_methods($interfaceName);

        return in_array($methodName, $interfaceMethods, true);
    }
}
