<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Rector\MethodCall;

use PhpParser\BuilderHelpers;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use Rector\Core\Rector\AbstractPHPUnitRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see https://github.com/sebastianbergmann/phpunit/issues/3494
 * @see https://github.com/sebastianbergmann/phpunit/issues/3495
 * @see \Rector\PHPUnit\Tests\Rector\MethodCall\ReplaceAssertArraySubsetRector\ReplaceAssertArraySubsetRectorTest
 */
final class ReplaceAssertArraySubsetRector extends AbstractPHPUnitRector
{
    /**
     * @var Expr[]
     */
    private $expectedKeys = [];

    /**
     * @var Expr[][]
     */
    private $expectedValuesWithKeys = [];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replace deprecated "assertArraySubset()" method with alternative methods', [
            new CodeSample(
                <<<'PHP'
class SomeTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $checkedArray = [];

        $this->assertArraySubset([
           'cache_directory' => 'new_value',
        ], $checkedArray, true);
    }
}
PHP
                ,
                <<<'PHP'
class SomeTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $checkedArray = [];

        $this->assertArrayHasKey('cache_directory', $checkedArray);
        $this->assertSame('new_value', $checkedArray['cache_directory']);
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
        return [MethodCall::class, StaticCall::class];
    }

    /**
     * @param MethodCall|StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isPHPUnitMethodName($node, 'assertArraySubset')) {
            return null;
        }

        $this->reset();

        $expectedArray = $this->matchArray($node->args[0]->value);
        if ($expectedArray === null) {
            return null;
        }

        $this->collectExpectedKeysAndValues($expectedArray);

        if ($this->expectedKeys === []) {
            // no keys → intersect!
            $arrayIntersect = new FuncCall(new Name('array_intersect'));
            $arrayIntersect->args[] = new Arg($expectedArray);
            $arrayIntersect->args[] = $node->args[1];

            $identical = new Identical($arrayIntersect, $expectedArray);

            $assertTrue = $this->createPHPUnitCallWithName($node, 'assertTrue');
            $assertTrue->args[] = new Arg($identical);

            $this->addNodeAfterNode($assertTrue, $node);
        } else {
            $this->addKeyAsserts($node);
            $this->addValueAsserts($node);
        }

        $this->removeNode($node);

        return null;
    }

    private function reset(): void
    {
        $this->expectedKeys = [];
        $this->expectedValuesWithKeys = [];
    }

    private function matchArray(Expr $expr): ?Array_
    {
        if ($expr instanceof Array_) {
            return $expr;
        }

        $value = $this->getValue($expr);

        // nothing we can do
        if ($value === null || ! is_array($value)) {
            return null;
        }

        // use specific array instead
        return BuilderHelpers::normalizeValue($value);
    }

    private function collectExpectedKeysAndValues(Array_ $expectedArray): void
    {
        foreach ($expectedArray->items as $arrayItem) {
            if ($arrayItem->key === null) {
                continue;
            }

            $this->expectedKeys[] = $arrayItem->key;

            $this->expectedValuesWithKeys[] = [
                'key' => $arrayItem->key,
                'value' => $arrayItem->value,
            ];
        }
    }

    /**
     * @param MethodCall|StaticCall $node
     */
    private function addKeyAsserts(Node $node): void
    {
        foreach ($this->expectedKeys as $expectedKey) {
            $assertArrayHasKey = $this->createPHPUnitCallWithName($node, 'assertArrayHasKey');
            $assertArrayHasKey->args[0] = new Arg($expectedKey);
            $assertArrayHasKey->args[1] = $node->args[1];

            $this->addNodeAfterNode($assertArrayHasKey, $node);
        }
    }

    /**
     * @param MethodCall|StaticCall $node
     */
    private function addValueAsserts(Node $node): void
    {
        $assertMethodName = $this->resolveAssertMethodName($node);

        foreach ($this->expectedValuesWithKeys as $expectedValueWithKey) {
            $expectedKey = $expectedValueWithKey['key'];
            $expectedValue = $expectedValueWithKey['value'];

            $assertSame = $this->createPHPUnitCallWithName($node, $assertMethodName);
            $assertSame->args[0] = new Arg($expectedValue);

            $arrayDimFetch = new ArrayDimFetch($node->args[1]->value, BuilderHelpers::normalizeValue($expectedKey));
            $assertSame->args[1] = new Arg($arrayDimFetch);

            $this->addNodeAfterNode($assertSame, $node);
        }
    }

    /**
     * @param MethodCall|StaticCall $node
     */
    private function resolveAssertMethodName(Node $node): string
    {
        if (! isset($node->args[2])) {
            return 'assertEquals';
        }

        $isStrict = $this->getValue($node->args[2]->value);

        return $isStrict ? 'assertSame' : 'assertEquals';
    }
}
