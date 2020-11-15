<?php

declare(strict_types=1);

namespace Rector\SOLID\Rector\Class_;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use Rector\Core\PhpParser\Node\Manipulator\ClassInsertManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\Util\StaticRectorStrings;
use Rector\NodeNestingScope\NodeFinder\ScopeAwareNodeFinder;

/**
 * @see \Rector\SOLID\Tests\Rector\Class_\RepeatedLiteralToClassConstantRector\RepeatedLiteralToClassConstantRectorTest
 */
final class RepeatedLiteralToClassConstantRector extends AbstractRector
{
    /**
     * @var string
     */
    private const VALUE = 'value';

    /**
     * @var int
     */
    private const MINIMAL_VALUE_OCCURRENCE = 3;

    /**
     * @var ClassInsertManipulator
     */
    private $classInsertManipulator;

    /**
     * @var ScopeAwareNodeFinder
     */
    private $scopeAwareNodeFinder;

    public function __construct(
        ClassInsertManipulator $classInsertManipulator,
        ScopeAwareNodeFinder $scopeAwareNodeFinder
    ) {
        $this->classInsertManipulator = $classInsertManipulator;
        $this->scopeAwareNodeFinder = $scopeAwareNodeFinder;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replace repeated strings with constant', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run($key, $items)
    {
        if ($key === 'requires') {
            return $items['requires'];
        }
    }
}
PHP
,
                <<<'PHP'
class SomeClass
{
    /**
     * @var string
     */
    private const REQUIRES = 'requires';
    public function run($key, $items)
    {
        if ($key === self::REQUIRES) {
            return $items[self::REQUIRES];
        }
    }
}
PHP

            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        // skip tests, where string values are often used as fixtures
        if ($this->isName($node, '*Test')) {
            return null;
        }

        /** @var String_[] $strings */
        $strings = $this->betterNodeFinder->findInstanceOf($node, String_::class);

        $stringsToReplace = $this->resolveStringsToReplace($strings);
        if ($stringsToReplace === []) {
            return null;
        }

        $this->replaceStringsWithClassConstReferences($node, $stringsToReplace);
        $this->addClassConsts($stringsToReplace, $node);

        return $node;
    }

    /**
     * @param String_[] $strings
     * @return string[]
     */
    private function resolveStringsToReplace(array $strings): array
    {
        $stringsByValue = [];
        foreach ($strings as $string) {
            if ($this->shouldSkipString($string)) {
                continue;
            }

            $stringsByValue[(string) $string->value][] = $string;
        }

        $stringsToReplace = [];

        foreach ($stringsByValue as $value => $strings) {
            if (count($strings) < self::MINIMAL_VALUE_OCCURRENCE) {
                continue;
            }

            $stringsToReplace[] = (string) $value;
        }

        return $stringsToReplace;
    }

    /**
     * @param string[] $stringsToReplace
     */
    private function addClassConsts(array $stringsToReplace, Class_ $class): void
    {
        foreach ($stringsToReplace as $stringToReplace) {
            $constantName = $this->createConstName($stringToReplace);

            $classConst = $this->nodeFactory->createPrivateClassConst($constantName, new String_($stringToReplace));

            $this->classInsertManipulator->addConstantToClass($class, $stringToReplace, $classConst);
        }
    }

    private function replaceStringsWithClassConstReferences(Class_ $class, array $stringsToReplace): void
    {
        $this->traverseNodesWithCallable($class, function (Node $node) use ($stringsToReplace) {
            if (! $node instanceof String_) {
                return null;
            }

            if (! $this->isValues($node, $stringsToReplace)) {
                return null;
            }

            $constantName = $this->createConstName($node->value);
            return new ClassConstFetch(new Name('self'), $constantName);
        });
    }

    private function shouldSkipString(String_ $string): bool
    {
        $value = (string) $string->value;

        // value is too short
        if (strlen($value) < 2) {
            return true;
        }

        if ($this->isNativeConstantResemblingValue($value)) {
            return true;
        }

        // is replaceable value?
        $matches = Strings::match($value, '#(?<' . self::VALUE . '>[\w\-\/\\_]+)#');
        if (! isset($matches[self::VALUE])) {
            return true;
        }

        // skip values in another constants
        $parentConst = $this->scopeAwareNodeFinder->findParentType($string, [ClassConst::class]);
        if ($parentConst !== null) {
            return true;
        }

        return $matches[self::VALUE] !== (string) $string->value;
    }

    private function createConstName(string $value): string
    {
        $value = StaticRectorStrings::camelCaseToUnderscore($value);
        $value = Strings::replace($value, '#[-\\\/]#', '_');

        return strtoupper($value);
    }

    private function isNativeConstantResemblingValue(string $value): bool
    {
        $loweredValue = strtolower($value);

        return in_array($loweredValue, ['true', 'false', 'bool', 'null'], true);
    }
}
