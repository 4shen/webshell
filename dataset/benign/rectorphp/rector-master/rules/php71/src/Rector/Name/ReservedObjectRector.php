<?php

declare(strict_types=1);

namespace Rector\Php71\Rector\Name;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Namespace_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see https://wiki.php.net/rfc/object-typehint
 * @see https://github.com/cebe/yii2/commit/9548a212ecf6e50fcdb0e5ba6daad88019cfc544
 * @see \Rector\Php71\Tests\Rector\Name\ReservedObjectRector\ReservedObjectRectorTest
 */
final class ReservedObjectRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $reservedKeywordsToReplacements = [];

    /**
     * @param string[] $reservedKeywordsToReplacements
     */
    public function __construct(array $reservedKeywordsToReplacements = [])
    {
        $this->reservedKeywordsToReplacements = $reservedKeywordsToReplacements;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Changes reserved "Object" name to "<Smart>Object" where <Smart> can be configured',
            [new CodeSample(<<<'PHP'
class Object
{
}
PHP
                , <<<'PHP'
class SmartObject
{
}
PHP
            )]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Identifier::class, Name::class];
    }

    /**
     * @param Identifier|Name $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Identifier) {
            return $this->processIdentifier($node);
        }

        return $this->processName($node);
    }

    private function processIdentifier(Identifier $identifier): Identifier
    {
        foreach ($this->reservedKeywordsToReplacements as $reservedKeyword => $replacement) {
            if (! $this->isName($identifier, $reservedKeyword)) {
                continue;
            }

            $identifier->name = $replacement;

            return $identifier;
        }

        return $identifier;
    }

    private function processName(Name $name): Name
    {
        // we look for "extends <Name>"
        $parentNode = $name->getAttribute(AttributeKey::PARENT_NODE);
        // "Object" can part of namespace name
        if ($parentNode instanceof Namespace_) {
            return $name;
        }

        // process lass part
        foreach ($this->reservedKeywordsToReplacements as $reservedKeyword => $replacement) {
            if (strtolower($name->getLast()) === strtolower($reservedKeyword)) {
                $name->parts[count($name->parts) - 1] = $replacement;

                // invoke override
                $name->setAttribute(AttributeKey::ORIGINAL_NODE, null);
            }
        }

        return $name;
    }
}
