<?php

declare(strict_types=1);

namespace Rector\Laravel\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\PhpParser\Node\Manipulator\ClassMethodManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see https://github.com/laravel/framework/pull/27276
 * @see \Rector\Laravel\Tests\Rector\StaticCall\RequestStaticValidateToInjectRector\RequestStaticValidateToInjectRectorTest
 */
final class RequestStaticValidateToInjectRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private const REQUEST_TYPES = ['Illuminate\Http\Request', 'Request'];

    /**
     * @var ClassMethodManipulator
     */
    private $classMethodManipulator;

    public function __construct(ClassMethodManipulator $classMethodManipulator)
    {
        $this->classMethodManipulator = $classMethodManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change static validate() method to $request->validate()', [
            new CodeSample(
                <<<'PHP'
use Illuminate\Http\Request;

class SomeClass
{
    public function store()
    {
        $validatedData = Request::validate(['some_attribute' => 'required']);
    }
}
PHP
                ,
                <<<'PHP'
use Illuminate\Http\Request;

class SomeClass
{
    public function store(\Illuminate\Http\Request $request)
    {
        $validatedData = $request->validate(['some_attribute' => 'required']);
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
        return [StaticCall::class, FuncCall::class];
    }

    /**
     * @param StaticCall|FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        $requestName = $this->classMethodManipulator->addMethodParameterIfMissing(
            $node,
            'Illuminate\Http\Request',
            ['request', 'httpRequest']
        );

        $variable = new Variable($requestName);

        $methodName = $this->getName($node->name);
        if ($methodName === null) {
            return null;
        }

        if ($node instanceof FuncCall) {
            if (count($node->args) === 0) {
                return $variable;
            }

            $methodName = 'input';
        }

        return new MethodCall($variable, new Identifier($methodName), $node->args);
    }

    /**
     * @param StaticCall|FuncCall $node
     */
    private function shouldSkip(Node $node): bool
    {
        if ($node instanceof StaticCall) {
            return ! $this->isObjectTypes($node, self::REQUEST_TYPES);
        }

        $class = $node->getAttribute(AttributeKey::CLASS_NODE);
        if (! $class instanceof Class_) {
            return true;
        }

        return ! $this->isName($node, 'request');
    }
}
