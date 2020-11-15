<?php

declare(strict_types=1);

namespace Rector\Core\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\Core\Tests\Rector\MethodCall\MethodCallToReturnRector\MethodCallToReturnRectorTest
 */
final class MethodCallToReturnRector extends AbstractRector
{
    /**
     * @var string[][]
     */
    private $methodNamesByType = [];

    /**
     * @param string[][] $methodNamesByType
     */
    public function __construct(array $methodNamesByType = [])
    {
        $this->methodNamesByType = $methodNamesByType;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Wrap method call to return', [
            new ConfiguredCodeSample(
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        $this->deny();
    }

    public function deny()
    {
        return 1;
    }
}
PHP
,
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        return $this->deny();
    }

    public function deny()
    {
        return 1;
    }
}
PHP

            , [
                '$methodNamesByType' => [
                    'SomeClass' => ['deny'],
                ],
            ]),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Expression::class];
    }

    /**
     * @param Expression $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $node->expr instanceof MethodCall) {
            return null;
        }

        $methodCall = $node->expr;

        return $this->refactorMethodCall($methodCall);
    }

    private function refactorMethodCall(MethodCall $methodCall): ?Node
    {
        foreach ($this->methodNamesByType as $methodType => $methodNames) {
            if (! $this->isObjectType($methodCall->var, $methodType)) {
                continue;
            }

            if (! $this->isNames($methodCall->name, $methodNames)) {
                continue;
            }

            $parentNode = $methodCall->getAttribute(AttributeKey::PARENT_NODE);

            // already wrapped
            if ($parentNode instanceof Return_) {
                continue;
            }

            $return = new Return_($methodCall);
            $methodCall->setAttribute(AttributeKey::PARENT_NODE, $return);

            return $return;
        }

        return null;
    }
}
