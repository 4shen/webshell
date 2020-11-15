<?php

declare(strict_types=1);

namespace Rector\Restoration\Rector\New_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name\FullyQualified;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;

/**
 * @sponsor Thanks https://amateri.com for sponsoring this rule - visit them on https://www.startupjobs.cz/startup/scrumworks-s-r-o
 *
 * @see \Rector\Restoration\Tests\Rector\New_\CompleteMissingDependencyInNewRector\CompleteMissingDependencyInNewRectorTest
 */
final class CompleteMissingDependencyInNewRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $classToInstantiateByType = [];

    /**
     * @param string[] $classToInstantiateByType
     */
    public function __construct(array $classToInstantiateByType = [])
    {
        $this->classToInstantiateByType = $classToInstantiateByType;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Complete missing constructor dependency instance by type', [
            new ConfiguredCodeSample(
                <<<'PHP'
final class SomeClass
{
    public function run()
    {
        $valueObject = new RandomValueObject();
    }
}

class RandomValueObject
{
    public function __construct(RandomDependency $randomDependency)
    {
    }
}
PHP
,
                <<<'PHP'
final class SomeClass
{
    public function run()
    {
        $valueObject = new RandomValueObject(new RandomDependency());
    }
}

class RandomValueObject
{
    public function __construct(RandomDependency $randomDependency)
    {
    }
}
PHP
                , [
                    '$classToInstantiateByType' => [
                        'RandomDependency' => 'RandomDependency',
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
        return [New_::class];
    }

    /**
     * @param New_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkipNew($node)) {
            return null;
        }

        /** @var ReflectionMethod $constructorMethodReflection */
        $constructorMethodReflection = $this->getNewNodeClassConstructorMethodReflection($node);

        foreach ($constructorMethodReflection->getParameters() as $position => $parameterReflection) {
            // argument is already set
            if (isset($node->args[$position])) {
                continue;
            }

            $classToInstantiate = $this->resolveClassToInstantiateByParameterReflection($parameterReflection);
            if ($classToInstantiate === null) {
                continue;
            }

            $new = new New_(new FullyQualified($classToInstantiate));
            $node->args[$position] = new Arg($new);
        }

        return $node;
    }

    private function shouldSkipNew(New_ $new): bool
    {
        $constructorMethodReflection = $this->getNewNodeClassConstructorMethodReflection($new);
        if ($constructorMethodReflection === null) {
            return true;
        }

        return $constructorMethodReflection->getNumberOfRequiredParameters() <= count($new->args);
    }

    private function getNewNodeClassConstructorMethodReflection(New_ $new): ?ReflectionMethod
    {
        $className = $this->getName($new->class);
        if ($className === null) {
            return null;
        }

        if (! class_exists($className)) {
            return null;
        }

        $reflectionClass = new ReflectionClass($className);

        return $reflectionClass->getConstructor();
    }

    private function resolveClassToInstantiateByParameterReflection(ReflectionParameter $reflectionParameter): ?string
    {
        $parameterType = $reflectionParameter->getType();
        if ($parameterType === null) {
            return null;
        }

        $requiredType = (string) $parameterType;

        return $this->classToInstantiateByType[$requiredType] ?? null;
    }
}
