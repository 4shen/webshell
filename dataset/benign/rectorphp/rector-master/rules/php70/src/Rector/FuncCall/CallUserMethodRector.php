<?php

declare(strict_types=1);

namespace Rector\Php70\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Php70\Tests\Rector\FuncCall\CallUserMethodRector\CallUserMethodRectorTest
 */
final class CallUserMethodRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private const OLD_TO_NEW_FUNCTIONS = [
        'call_user_method' => 'call_user_func',
        'call_user_method_array' => 'call_user_func_array',
    ];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Changes call_user_method()/call_user_method_array() to call_user_func()/call_user_func_array()',
            [new CodeSample(
                'call_user_method($method, $obj, "arg1", "arg2");',
                'call_user_func(array(&$obj, "method"), "arg1", "arg2");'
            )]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }

    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isNames($node, array_keys(self::OLD_TO_NEW_FUNCTIONS))) {
            return null;
        }

        $newName = self::OLD_TO_NEW_FUNCTIONS[$this->getName($node)];
        $node->name = new Name($newName);

        $argNodes = $node->args;

        $node->args[0] = $this->createArg([$argNodes[1]->value, $argNodes[0]->value]);
        unset($node->args[1]);

        return $node;
    }
}
