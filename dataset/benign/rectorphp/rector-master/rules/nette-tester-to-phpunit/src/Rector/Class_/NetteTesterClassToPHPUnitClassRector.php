<?php

declare(strict_types=1);

namespace Rector\NetteTesterToPHPUnit\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\Include_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Tester\TestCase;

/**
 * @see \Rector\NetteTesterToPHPUnit\Tests\Rector\Class_\NetteTesterClassToPHPUnitClassRector\NetteTesterPHPUnitRectorTest
 */
final class NetteTesterClassToPHPUnitClassRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Migrate Nette Tester test case to PHPUnit', [
            new CodeSample(
                <<<'PHP'
namespace KdybyTests\Doctrine;

use Tester\TestCase;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';

class ExtensionTest extends TestCase
{
    public function testFunctionality()
    {
        Assert::true($default instanceof Kdyby\Doctrine\EntityManager);
        Assert::true(5);
        Assert::same($container->getService('kdyby.doctrine.default.entityManager'), $default);
    }
}

(new \ExtensionTest())->run();
PHP
                ,
                <<<'PHP'
namespace KdybyTests\Doctrine;

use Tester\TestCase;
use Tester\Assert;

class ExtensionTest extends \PHPUnit\Framework\TestCase
{
    public function testFunctionality()
    {
        $this->assertInstanceOf(\Kdyby\Doctrine\EntityManager::cllass, $default);
        $this->assertTrue(5);
        $this->same($container->getService('kdyby.doctrine.default.entityManager'), $default);
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
        return [Class_::class, Include_::class, MethodCall::class];
    }

    /**
     * @param Class_|Include_|MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Include_) {
            $this->processAboveTestInclude($node);
            return null;
        }

        if (! $this->isObjectType($node, TestCase::class)) {
            return null;
        }

        if ($node instanceof MethodCall) {
            $this->processUnderTestRun($node);
            return null;
        }

        $this->processExtends($node);
        $this->processMethods($node);

        return $node;
    }

    private function processAboveTestInclude(Include_ $include): void
    {
        if ($include->getAttribute(AttributeKey::CLASS_NODE) === null) {
            $this->removeNode($include);
        }
    }

    private function processUnderTestRun(MethodCall $methodCall): void
    {
        if ($this->isName($methodCall->name, 'run')) {
            $this->removeNode($methodCall);
        }
    }

    private function processExtends(Class_ $class): void
    {
        $class->extends = new FullyQualified('PHPUnit\Framework\TestCase');
    }

    private function processMethods(Class_ $class): void
    {
        foreach ($class->getMethods() as $classMethod) {
            if ($this->isNames($classMethod, ['setUp', 'tearDown'])) {
                $this->makeProtected($classMethod);
            }
        }
    }
}
