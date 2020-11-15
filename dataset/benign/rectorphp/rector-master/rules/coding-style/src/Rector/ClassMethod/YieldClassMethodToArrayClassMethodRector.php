<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\Yield_;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\PhpParser\NodeTransformer;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see https://medium.com/tech-tajawal/use-memory-gently-with-yield-in-php-7e62e2480b8d
 * @see https://3v4l.org/5PJid
 * @see \Rector\CodingStyle\Tests\Rector\ClassMethod\YieldClassMethodToArrayClassMethodRector\YieldClassMethodToArrayClassMethodRectorTest
 */
final class YieldClassMethodToArrayClassMethodRector extends AbstractRector
{
    /**
     * @var string[][]
     */
    private $methodsByType = [];

    /**
     * @var NodeTransformer
     */
    private $nodeTransformer;

    /**
     * @param string[][] $methodsByType
     */
    public function __construct(NodeTransformer $nodeTransformer, array $methodsByType = [])
    {
        $this->methodsByType = $methodsByType;
        $this->nodeTransformer = $nodeTransformer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns yield return to array return in specific type and method', [
            new ConfiguredCodeSample(
                <<<'PHP'
class SomeEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        yield 'event' => 'callback';
    }
}
PHP
                ,
                <<<'PHP'
class SomeEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return ['event' => 'callback'];
    }
}
PHP
                ,
                [
                    'EventSubscriberInterface' => ['getSubscribedEvents'],
                ]
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($this->methodsByType as $type => $methods) {
            if (! $this->isObjectType($node, $type)) {
                continue;
            }

            foreach ($methods as $method) {
                if (! $this->isName($node, $method)) {
                    continue;
                }

                $yieldNodes = $this->collectYieldNodesFromClassMethod($node);
                if ($yieldNodes === []) {
                    continue;
                }

                $arrayNode = $this->nodeTransformer->transformYieldsToArray($yieldNodes);
                $this->removeNodes($yieldNodes);

                $node->returnType = new Identifier('array');

                $returnExpression = new Return_($arrayNode);
                $node->stmts = array_merge((array) $node->stmts, [$returnExpression]);
            }
        }

        return $node;
    }

    /**
     * @return Yield_[]
     */
    private function collectYieldNodesFromClassMethod(ClassMethod $classMethod): array
    {
        $yieldNodes = [];

        if ($classMethod->stmts === null) {
            return [];
        }

        foreach ($classMethod->stmts as $statement) {
            if (! $statement instanceof Expression) {
                continue;
            }

            if ($statement->expr instanceof Yield_) {
                $yieldNodes[] = $statement->expr;
            }
        }

        return $yieldNodes;
    }
}
