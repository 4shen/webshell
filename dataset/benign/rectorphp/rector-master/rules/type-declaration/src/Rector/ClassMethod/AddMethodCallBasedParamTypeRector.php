<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeCollector\NodeFinder\MethodCallParsedNodesFinder;
use Rector\NodeCollector\ValueObject\ArrayCallable;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;

/**
 * @sponsor Thanks https://spaceflow.io/ for sponsoring this rule - visit them on https://github.com/SpaceFlow-app
 *
 * @see \Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddMethodCallBasedParamTypeRector\AddMethodCallBasedParamTypeRectorTest
 */
final class AddMethodCallBasedParamTypeRector extends AbstractRector
{
    /**
     * @var TypeFactory
     */
    private $typeFactory;

    /**
     * @var MethodCallParsedNodesFinder
     */
    private $methodCallParsedNodesFinder;

    public function __construct(TypeFactory $typeFactory, MethodCallParsedNodesFinder $methodCallParsedNodesFinder)
    {
        $this->typeFactory = $typeFactory;
        $this->methodCallParsedNodesFinder = $methodCallParsedNodesFinder;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change param type of passed getId() to UuidInterface type declaration', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function getById($id)
    {
    }
}

class CallerClass
{
    public function run()
    {
        $building = new Building();
        $someClass = new SomeClass();
        $someClass->getById($building->getId());
    }
}
PHP
                ,
                <<<'PHP'
class SomeClass
{
    public function getById(\Ramsey\Uuid\UuidInterface $id)
    {
    }
}

class CallerClass
{
    public function run()
    {
        $building = new Building();
        $someClass = new SomeClass();
        $someClass->getById($building->getId());
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
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        $classMethodCalls = $this->methodCallParsedNodesFinder->findByClassMethod($node);
        $classParameterTypes = $this->getCallTypesByPosition($classMethodCalls);

        foreach ($classParameterTypes as $position => $argumentStaticType) {
            if ($this->skipArgumentStaticType($node, $argumentStaticType, $position)) {
                continue;
            }

            $phpParserTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($argumentStaticType);

            // update parameter
            $node->params[$position]->type = $phpParserTypeNode;
        }

        return $node;
    }

    /**
     * @param MethodCall[]|StaticCall[]|ArrayCallable[] $classMethodCalls
     * @return Type[]
     */
    private function getCallTypesByPosition(array $classMethodCalls): array
    {
        $staticTypesByArgumentPosition = [];
        foreach ($classMethodCalls as $classMethodCall) {
            if (! $classMethodCall instanceof StaticCall && ! $classMethodCall instanceof MethodCall) {
                continue;
            }

            foreach ($classMethodCall->args as $position => $arg) {
                $staticTypesByArgumentPosition[$position][] = $this->getStaticType($arg->value);
            }
        }

        // unite to single type
        $staticTypeByArgumentPosition = [];
        foreach ($staticTypesByArgumentPosition as $position => $staticTypes) {
            $staticTypeByArgumentPosition[$position] = $this->typeFactory->createMixedPassedOrUnionType($staticTypes);
        }

        return $staticTypeByArgumentPosition;
    }

    private function skipArgumentStaticType(Node $node, Type $argumentStaticType, int $position): bool
    {
        if ($argumentStaticType instanceof MixedType) {
            return true;
        }

        if (! isset($node->params[$position])) {
            return true;
        }

        $parameter = $node->params[$position];
        if ($parameter->type === null) {
            return false;
        }

        $parameterStaticType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($parameter->type);
        // already completed → skip
        return $parameterStaticType->equals($argumentStaticType);
    }
}
