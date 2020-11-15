<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Naming;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Namespace_;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Util\StaticRectorStrings;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\PackageBuilder\Strings\StringFormatConverter;

final class PhpSpecRenaming
{
    /**
     * @var string
     */
    private const SPEC = 'Spec';

    /**
     * @var StringFormatConverter
     */
    private $stringFormatConverter;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(StringFormatConverter $stringFormatConverter, NodeNameResolver $nodeNameResolver)
    {
        $this->stringFormatConverter = $stringFormatConverter;
        $this->nodeNameResolver = $nodeNameResolver;
    }

    public function renameMethod(ClassMethod $classMethod): void
    {
        $name = $this->nodeNameResolver->getName($classMethod);
        if ($name === null) {
            return;
        }

        if ($classMethod->isPrivate()) {
            return;
        }

        $name = $this->removeNamePrefixes($name);

        // from PhpSpec to PHPUnit method naming convention
        $name = $this->stringFormatConverter->underscoreAndHyphenToCamelCase($name);

        // add "test", so PHPUnit runs the method
        if (! Strings::startsWith($name, 'test')) {
            $name = 'test' . ucfirst($name);
        }

        $classMethod->name = new Identifier($name);
    }

    public function renameExtends(Class_ $class): void
    {
        $class->extends = new FullyQualified('PHPUnit\Framework\TestCase');
    }

    public function renameNamespace(Class_ $class): void
    {
        /** @var Namespace_ $namespace */
        $namespace = $class->getAttribute(AttributeKey::NAMESPACE_NODE);
        if ($namespace->name === null) {
            return;
        }

        $newNamespaceName = StaticRectorStrings::removePrefixes($namespace->name->toString(), ['spec\\']);

        $namespace->name = new Name('Tests\\' . $newNamespaceName);
    }

    public function renameClass(Class_ $class): void
    {
        // anonymous class?
        if ($class->name === null) {
            throw new ShouldNotHappenException();
        }

        // 2. change class name
        $newClassName = StaticRectorStrings::removeSuffixes($class->name->toString(), [self::SPEC]);
        $newTestClassName = $newClassName . 'Test';

        $class->name = new Identifier($newTestClassName);
    }

    public function resolveObjectPropertyName(Class_ $class): string
    {
        // anonymous class?
        if ($class->name === null) {
            throw new ShouldNotHappenException();
        }

        $bareClassName = StaticRectorStrings::removeSuffixes($class->name->toString(), [self::SPEC, 'Test']);

        return lcfirst($bareClassName);
    }

    public function resolveTestedClass(Node $node): string
    {
        /** @var string $className */
        $className = $node->getAttribute(AttributeKey::CLASS_NAME);

        $newClassName = StaticRectorStrings::removePrefixes($className, ['spec\\']);

        return StaticRectorStrings::removeSuffixes($newClassName, [self::SPEC]);
    }

    private function removeNamePrefixes(string $name): string
    {
        $originalName = $name;

        $name = StaticRectorStrings::removePrefixes(
            $name,
            ['it_should_have_', 'it_should_be', 'it_should_', 'it_is_', 'it_', 'is_']
        );

        return $name ?: $originalName;
    }
}
