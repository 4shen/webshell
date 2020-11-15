<?php

declare(strict_types=1);

namespace Rector\Renaming\Rector\Namespace_;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use Rector\Core\Naming\NamespaceMatcher;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\ValueObject\RenamedNamespaceValueObject;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\Renaming\Tests\Rector\Namespace_\RenameNamespaceRector\RenameNamespaceRectorTest
 */
final class RenameNamespaceRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $oldToNewNamespaces = [];

    /**
     * @var NamespaceMatcher
     */
    private $namespaceMatcher;

    /**
     * @param string[] $oldToNewNamespaces
     */
    public function __construct(NamespaceMatcher $namespaceMatcher, array $oldToNewNamespaces = [])
    {
        $this->oldToNewNamespaces = $oldToNewNamespaces;
        $this->namespaceMatcher = $namespaceMatcher;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replaces old namespace by new one.', [
            new ConfiguredCodeSample(
                '$someObject = new SomeOldNamespace\SomeClass;',
                '$someObject = new SomeNewNamespace\SomeClass;',
                [
                    '$oldToNewNamespaces' => [
                        'SomeOldNamespace' => 'SomeNewNamespace',
                    ],
                ]
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Namespace_::class, Use_::class, Name::class];
    }

    /**
     * @param Namespace_|Use_|Name $node
     */
    public function refactor(Node $node): ?Node
    {
        $name = $this->getName($node);
        if ($name === null) {
            return null;
        }

        $renamedNamespaceValueObject = $this->namespaceMatcher->matchRenamedNamespace($name, $this->oldToNewNamespaces);
        if ($renamedNamespaceValueObject === null) {
            return null;
        }

        if ($this->isClassFullyQualifiedName($node)) {
            return null;
        }

        if ($node instanceof Namespace_) {
            $newName = $renamedNamespaceValueObject->getNameInNewNamespace();
            $node->name = new Name($newName);

            return $node;
        }

        if ($node instanceof Use_) {
            $newName = $renamedNamespaceValueObject->getNameInNewNamespace();
            $node->uses[0]->name = new Name($newName);

            return $node;
        }

        $newName = $this->isPartialNamespace($node) ? $this->resolvePartialNewName(
            $node,
            $renamedNamespaceValueObject
        ) : $renamedNamespaceValueObject->getNameInNewNamespace();
        if ($newName === null) {
            return null;
        }

        return new FullyQualified($newName);
    }

    /**
     * Checks for "new \ClassNoNamespace;"
     * This should be skipped, not a namespace.
     */
    private function isClassFullyQualifiedName(Node $node): bool
    {
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode === null) {
            return false;
        }

        if (! $parentNode instanceof New_) {
            return false;
        }

        /** @var FullyQualified $fullyQualifiedNode */
        $fullyQualifiedNode = $parentNode->class;

        $newClassName = $fullyQualifiedNode->toString();

        return array_key_exists($newClassName, $this->oldToNewNamespaces);
    }

    private function isPartialNamespace(Name $name): bool
    {
        $resolvedName = $name->getAttribute(AttributeKey::RESOLVED_NAME);
        if ($resolvedName === null) {
            return false;
        }

        if ($resolvedName instanceof FullyQualified) {
            return ! $this->isName($name, $resolvedName->toString());
        }

        return false;
    }

    private function resolvePartialNewName(
        Name $name,
        RenamedNamespaceValueObject $renamedNamespaceValueObject
    ): ?string {
        $nodeName = $this->getName($name);
        if ($nodeName === null) {
            return null;
        }

        $completeNewName = $renamedNamespaceValueObject->getNameInNewNamespace();

        // first dummy implementation - improve
        $cutOffFromTheLeft = Strings::length($completeNewName) - Strings::length($name->toString());

        return Strings::substring($completeNewName, $cutOffFromTheLeft);
    }
}
