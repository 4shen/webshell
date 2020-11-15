<?php

declare(strict_types=1);

namespace Rector\Php53\Rector\Variable;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @sponsor Thanks https://twitter.com/afilina & Zenika (CAN) for sponsoring this rule - visit them on https://zenika.ca/en/en
 *
 * @see \Rector\Php53\Tests\Rector\Variable\ReplaceHttpServerVarsByServerRector\ReplaceHttpServerVarsByServerRectorTest
 * @see https://blog.tigertech.net/posts/php-5-3-http-server-vars/
 */
final class ReplaceHttpServerVarsByServerRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private const VARIABLE_RENAME_MAP = [
        'HTTP_SERVER_VARS' => '_SERVER',
        'HTTP_GET_VARS' => '_GET',
        'HTTP_POST_VARS' => '_POST',
        'HTTP_POST_FILES' => '_FILES',
        'HTTP_SESSION_VARS' => '_SESSION',
        'HTTP_ENV_VARS' => '_ENV',
        'HTTP_COOKIE_VARS' => '_COOKIE',
    ];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Rename old $HTTP_* variable names to new replacements', [
            new CodeSample('$serverVars = $HTTP_SERVER_VARS;', '$serverVars = $_SERVER;'),
        ]);
    }

    public function getNodeTypes(): array
    {
        return [Variable::class];
    }

    /**
     * @param Variable $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach (self::VARIABLE_RENAME_MAP as $oldName => $newName) {
            if (! $this->isName($node, $oldName)) {
                continue;
            }

            $node->name = $newName;
            return $node;
        }

        return null;
    }
}
