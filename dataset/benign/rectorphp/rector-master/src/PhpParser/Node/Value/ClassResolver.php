<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Node\Value;

use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\NodeNameResolver\NodeNameResolver;

final class ClassResolver
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }

    public function getClassFromMethodCall(MethodCall $methodCall): ?FullyQualified
    {
        $class = null;
        $previousExpression = $methodCall->getAttribute('previousExpression');

        // [PhpParser\Node\Expr\Assign] $variable = new Class()
        if ($previousExpression instanceof Expression) {
            $class = $this->resolveFromExpression($previousExpression);
        }

        if ($previousExpression instanceof ClassMethod) {
            $class = $this->resolveFromClassMethod($previousExpression, $methodCall);
        }

        return $class;
    }

    private function resolveFromExpression(Expression $expression): ?FullyQualified
    {
        $assign = $expression->expr;
        if (! $assign instanceof Assign) {
            return null;
        }

        $new = $assign->expr;
        if (! $new instanceof New_) {
            return null;
        }

        $class = $new->class;

        return $class instanceof FullyQualified ? $class : null;
    }

    private function resolveFromClassMethod(ClassMethod $classMethod, MethodCall $methodCall): ?FullyQualified
    {
        $var = $methodCall->var;
        if (! $var instanceof Variable) {
            return null;
        }

        return $this->nodeNameResolver->isName($var, 'this')
            ? $this->tryToResolveClassMethodFromThis($classMethod)
            : $this->tryToResolveClassMethodParams($classMethod, $methodCall);
    }

    private function tryToResolveClassMethodFromThis(ClassMethod $classMethod): ?FullyQualified
    {
        $class = $classMethod->name->getAttribute(ClassLike::class)->name;

        if (! $class instanceof Identifier) {
            return null;
        }

        return new FullyQualified($class->getAttribute('className'));
    }

    private function tryToResolveClassMethodParams(ClassMethod $classMethod, MethodCall $methodCall): ?FullyQualified
    {
        // $ param -> method();
        $params = $classMethod->params;
        /** @var Param $param */
        foreach ($params as $param) {
            $paramVar = $param->var;
            $methodCallVar = $methodCall->var;
            if (! $paramVar instanceof Variable || ! $methodCallVar instanceof Variable) {
                continue;
            }
            if ($paramVar->name === $methodCallVar->name) {
                $class = $param->type;
                return $class instanceof FullyQualified ? $class : null;
            }
        }

        return null;
    }
}
