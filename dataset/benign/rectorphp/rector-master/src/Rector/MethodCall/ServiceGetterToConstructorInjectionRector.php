<?php

declare(strict_types=1);

namespace Rector\Core\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\ObjectType;
use Rector\Core\Naming\PropertyNaming;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\Core\Tests\Rector\MethodCall\ServiceGetterToConstructorInjectionRector\ServiceGetterToConstructorInjectionRectorTest
 * @see \Rector\Core\Tests\Rector\MethodCall\ServiceGetterToConstructorInjectionRector\ServiceGetterToConstructorInjectionRectorTest
 */
final class ServiceGetterToConstructorInjectionRector extends AbstractRector
{
    /**
     * @var mixed[]
     */
    private $methodNamesByTypesToServiceTypes = [];

    /**
     * @var PropertyNaming
     */
    private $propertyNaming;

    /**
     * @param mixed[] $methodNamesByTypesToServiceTypes
     */
    public function __construct(PropertyNaming $propertyNaming, array $methodNamesByTypesToServiceTypes = [])
    {
        $this->methodNamesByTypesToServiceTypes = $methodNamesByTypesToServiceTypes;
        $this->propertyNaming = $propertyNaming;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Get service call to constructor injection', [
            new ConfiguredCodeSample(
                <<<'PHP'
final class SomeClass
{
    /**
     * @var FirstService
     */
    private $firstService;

    public function __construct(FirstService $firstService)
    {
        $this->firstService = $firstService;
    }

    public function run()
    {
        $anotherService = $this->firstService->getAnotherService();
        $anotherService->run();
    }
}

class FirstService
{
    /**
     * @var AnotherService
     */
    private $anotherService;

    public function __construct(AnotherService $anotherService)
    {
        $this->anotherService = $anotherService;
    }

    public function getAnotherService(): AnotherService
    {
         return $this->anotherService;
    }
}
PHP
                ,
                <<<'PHP'
final class SomeClass
{
    /**
     * @var FirstService
     */
    private $firstService;

    /**
     * @var AnotherService
     */
    private $anotherService;

    public function __construct(FirstService $firstService, AnotherService $anotherService)
    {
        $this->firstService = $firstService;
        $this->anotherService = $anotherService;
    }

    public function run()
    {
        $anotherService = $this->anotherService;
        $anotherService->run();
    }
}
PHP

                ,
                [
                    '$methodNamesByTypesToServiceTypes' => [
                        'FirstService' => [
                            'getAnotherService' => 'AnotherService',
                        ],
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
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        $classNode = $node->getAttribute(AttributeKey::CLASS_NODE);
        if (! $this->isNonAnonymousClass($classNode)) {
            return null;
        }

        foreach ($this->methodNamesByTypesToServiceTypes as $type => $methodNamesToServiceTypes) {
            if (! $this->isObjectType($node->var, $type)) {
                continue;
            }

            foreach ($methodNamesToServiceTypes as $methodName => $serviceType) {
                if (! $this->isName($node->name, $methodName)) {
                    continue;
                }

                $serviceObjectType = new ObjectType($serviceType);

                $propertyName = $this->propertyNaming->fqnToVariableName($serviceObjectType);

                /** @var Class_ $classNode */
                $this->addPropertyToClass($classNode, $serviceObjectType, $propertyName);

                return new PropertyFetch(new Variable('this'), new Identifier($propertyName));
            }
        }

        return $node;
    }
}
