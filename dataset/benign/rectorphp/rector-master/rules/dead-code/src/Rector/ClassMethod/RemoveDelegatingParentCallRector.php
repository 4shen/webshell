<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;

/**
 * @see \Rector\DeadCode\Tests\Rector\ClassMethod\RemoveDelegatingParentCallRector\RemoveDelegatingParentCallRectorTest
 */
final class RemoveDelegatingParentCallRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Removed dead parent call, that does not change anything', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function prettyPrint(array $stmts): string
    {
        return parent::prettyPrint($stmts);
    }
}
PHP
                ,
                <<<'PHP'
class SomeClass
{
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
        $classNode = $node->getAttribute(AttributeKey::CLASS_NODE);
        if ($this->shouldSkipClass($classNode)) {
            return null;
        }

        if ($node->stmts === null || count((array) $node->stmts) !== 1) {
            return null;
        }

        $stmtsValues = array_values($node->stmts);
        $onlyStmt = $this->unwrapExpression($stmtsValues[0]);

        // are both return?
        if ($this->isMethodReturnType($node, 'void') && ! $onlyStmt instanceof Return_) {
            return null;
        }

        $staticCall = $this->matchStaticCall($onlyStmt);
        if (! $this->isParentCallMatching($node, $staticCall)) {
            return null;
        }

        if ($this->hasRequiredAnnotation($node)) {
            return null;
        }

        // the method is just delegation, nothing extra
        $this->removeNode($node);

        return null;
    }

    private function shouldSkipClass(?ClassLike $classLike): bool
    {
        if (! $classLike instanceof Class_) {
            return true;
        }
        return $classLike->extends === null;
    }

    /**
     * @param Node|Expression $node
     */
    private function unwrapExpression(Node $node): Node
    {
        if ($node instanceof Expression) {
            return $node->expr;
        }

        return $node;
    }

    private function isMethodReturnType(ClassMethod $classMethod, string $type): bool
    {
        if ($classMethod->returnType === null) {
            return false;
        }

        return $this->isName($classMethod->returnType, $type);
    }

    private function matchStaticCall(Node $node): ?StaticCall
    {
        // must be static call
        if ($node instanceof Return_) {
            if ($node->expr instanceof StaticCall) {
                return $node->expr;
            }

            return null;
        }

        if ($node instanceof StaticCall) {
            return $node;
        }

        return null;
    }

    private function isParentCallMatching(ClassMethod $classMethod, ?StaticCall $staticCall): bool
    {
        if ($staticCall === null) {
            return false;
        }

        if (! $this->areNamesEqual($staticCall->name, $classMethod->name)) {
            return false;
        }

        if (! $this->isName($staticCall->class, 'parent')) {
            return false;
        }

        if (! $this->areArgsAndParamsEqual($staticCall->args, $classMethod->params)) {
            return false;
        }
        return ! $this->isParentClassMethodVisibilityOrDefaultOverride($classMethod, $staticCall);
    }

    private function hasRequiredAnnotation(Node $node): bool
    {
        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return false;
        }

        return (bool) $phpDocInfo->hasByName('required');
    }

    /**
     * @param Arg[] $args
     * @param Param[] $params
     */
    private function areArgsAndParamsEqual(array $args, array $params): bool
    {
        if (count($args) !== count($params)) {
            return false;
        }

        foreach ($args as $key => $arg) {
            if (! isset($params[$key])) {
                return false;
            }

            $param = $params[$key];

            if (! $this->areNodesEqual($param->var, $arg->value)) {
                return false;
            }
        }

        return true;
    }

    private function isParentClassMethodVisibilityOrDefaultOverride(
        ClassMethod $classMethod,
        StaticCall $staticCall
    ): bool {
        /** @var string $className */
        $className = $staticCall->getAttribute(AttributeKey::CLASS_NAME);

        $parentClassName = get_parent_class($className);
        if (! $parentClassName) {
            throw new ShouldNotHappenException();
        }

        /** @var string $methodName */
        $methodName = $this->getName($staticCall->name);

        $parentClassMethod = $this->functionLikeParsedNodesFinder->findMethod($methodName, $parentClassName);
        if ($parentClassMethod !== null && $parentClassMethod->isProtected() && $classMethod->isPublic()) {
            return true;
        }

        return $this->checkOverrideUsingReflection($classMethod, $parentClassName, $methodName);
    }

    private function checkOverrideUsingReflection(
        ClassMethod $classMethod,
        string $parentClassName,
        string $methodName
    ): bool {
        $parentMethodReflection = $this->getReflectionMethod($parentClassName, $methodName);
        // 3rd party code
        if ($parentMethodReflection !== null) {
            if ($parentMethodReflection->isProtected() && $classMethod->isPublic()) {
                return true;
            }
            if ($parentMethodReflection->isInternal()) {
                //we can't know for certain so we assume its an override
                return true;
            }
            if ($this->areParameterDefaultsDifferent($classMethod, $parentMethodReflection)) {
                return true;
            }
        }

        return false;
    }

    private function getReflectionMethod(string $className, string $methodName): ?ReflectionMethod
    {
        if (! method_exists($className, $methodName)) {
            //internal classes don't have __construct method
            if ($methodName === '__construct' && class_exists($className)) {
                return (new ReflectionClass($className))->getConstructor();
            }
            return null;
        }
        return new ReflectionMethod($className, $methodName);
    }

    private function areParameterDefaultsDifferent(
        ClassMethod $classMethod,
        ReflectionMethod $reflectionMethod
    ): bool {
        foreach ($reflectionMethod->getParameters() as $key => $parameter) {
            if (! isset($classMethod->params[$key])) {
                if ($parameter->isDefaultValueAvailable()) {
                    continue;
                }
                return true;
            }

            $methodParam = $classMethod->params[$key];

            if ($this->areDefaultValuesDifferent($parameter, $methodParam)) {
                return true;
            }
        }
        return false;
    }

    private function areDefaultValuesDifferent(ReflectionParameter $reflectionParameter, Param $methodParam): bool
    {
        if ($reflectionParameter->isDefaultValueAvailable() !== isset($methodParam->default)) {
            return true;
        }

        return $reflectionParameter->isDefaultValueAvailable() && $methodParam->default !== null &&
            ! $this->isValue($methodParam->default, $reflectionParameter->getDefaultValue());
    }
}
