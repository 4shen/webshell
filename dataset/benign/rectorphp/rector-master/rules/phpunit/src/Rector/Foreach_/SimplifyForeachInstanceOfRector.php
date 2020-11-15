<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Rector\Foreach_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Foreach_;
use Rector\Core\PhpParser\Node\Manipulator\ForeachManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\PHPUnit\Tests\Rector\Foreach_\SimplifyForeachInstanceOfRector\SimplifyForeachInstanceOfRectorTest
 */
final class SimplifyForeachInstanceOfRector extends AbstractRector
{
    /**
     * @var ForeachManipulator
     */
    private $foreachManipulator;

    public function __construct(ForeachManipulator $foreachManipulator)
    {
        $this->foreachManipulator = $foreachManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Simplify unnecessary foreach check of instances', [
            new CodeSample(
                <<<'PHP'
foreach ($foos as $foo) {
    $this->assertInstanceOf(\SplFileInfo::class, $foo);
}
PHP
                ,
                '$this->assertContainsOnlyInstancesOf(\SplFileInfo::class, $foos);'
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Foreach_::class];
    }

    /**
     * @param Foreach_ $node
     */
    public function refactor(Node $node): ?Node
    {
        /** @var MethodCall|StaticCall|null $matchedNode */
        $matchedNode = $this->foreachManipulator->matchOnlyStmt(
            $node,
            function (Node $node, Foreach_ $foreachNode): ?Node {
                if (! $node instanceof MethodCall && ! $node instanceof StaticCall) {
                    return null;
                }

                if (! $this->isName($node->name, 'assertInstanceOf')) {
                    return null;
                }

                if (! $this->areNodesEqual($foreachNode->valueVar, $node->args[1]->value)) {
                    return null;
                }

                return $node;
            }
        );

        if ($matchedNode === null) {
            return null;
        }

        /** @var MethodCall|StaticCall $matchedNode */
        $callClass = get_class($matchedNode);

        return new $callClass(
            $this->resolveVar($matchedNode),
            new Name('assertContainsOnlyInstancesOf'),
            [$matchedNode->args[0], new Arg($node->expr)]
        );
    }

    /**
     * @param MethodCall|StaticCall $node
     */
    private function resolveVar(Node $node): Node
    {
        if ($node instanceof MethodCall) {
            return $node->var;
        }

        return $node->class;
    }
}
