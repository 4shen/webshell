<?php

declare(strict_types=1);

namespace Rector\Php71\Rector\FuncCall;

use function count;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PHPStan\Reflection\ParametersAcceptor;
use Rector\Core\PHPStan\Reflection\CallReflectionResolver;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see https://www.reddit.com/r/PHP/comments/a1ie7g/is_there_a_linter_for_argumentcounterror_for_php/
 * @see http://php.net/manual/en/class.argumentcounterror.php
 *
 * @see \Rector\Php71\Tests\Rector\FuncCall\RemoveExtraParametersRector\RemoveExtraParametersRectorTest
 */
final class RemoveExtraParametersRector extends AbstractRector
{
    /**
     * @var CallReflectionResolver
     */
    private $callReflectionResolver;

    public function __construct(CallReflectionResolver $callReflectionResolver)
    {
        $this->callReflectionResolver = $callReflectionResolver;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove extra parameters', [
            new CodeSample('strlen("asdf", 1);', 'strlen("asdf");'),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class, MethodCall::class, StaticCall::class];
    }

    /**
     * @param FuncCall|MethodCall|StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        /** @var ParametersAcceptor $parametersAcceptor */
        $parametersAcceptor = $this->callReflectionResolver->resolveParametersAcceptor(
            $this->callReflectionResolver->resolveCall($node),
            $node
        );

        $numberOfParameters = count($parametersAcceptor->getParameters());
        $numberOfArguments = count($node->args);

        for ($i = $numberOfParameters; $i <= $numberOfArguments; $i++) {
            unset($node->args[$i]);
        }

        return $node;
    }

    /**
     * @param FuncCall|MethodCall|StaticCall $node
     */
    private function shouldSkip(Node $node): bool
    {
        if (count($node->args) === 0) {
            return true;
        }

        if ($node instanceof StaticCall) {
            if (! $node->class instanceof Name) {
                return true;
            }

            if ($this->isName($node->class, 'parent')) {
                return true;
            }
        }

        $parametersAcceptor = $this->callReflectionResolver->resolveParametersAcceptor(
            $this->callReflectionResolver->resolveCall($node),
            $node
        );
        if ($parametersAcceptor === null) {
            return true;
        }

        // can be any number of arguments → nothing to limit here
        if ($parametersAcceptor->isVariadic()) {
            return true;
        }

        return count($parametersAcceptor->getParameters()) >= count($node->args);
    }
}
