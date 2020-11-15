<?php

declare(strict_types=1);

namespace Rector\MysqlToMysqli\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see https://stackoverflow.com/a/34041762/1348344
 * @see \Rector\MysqlToMysqli\Tests\Rector\FuncCall\MysqlPConnectToMysqliConnectRector\MysqlPConnectToMysqliConnectRectorTest
 */
final class MysqlPConnectToMysqliConnectRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replace mysql_pconnect() with mysqli_connect() with host p: prefix', [
            new CodeSample(
                <<<'PHP'
final class SomeClass
{
    public function run($host, $username, $password)
    {
        return mysql_pconnect($host, $username, $password);
    }
}
PHP
                ,
                <<<'PHP'
final class SomeClass
{
    public function run($host, $username, $password)
    {
        return mysqli_connect('p:' . $host, $username, $password);
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
        return [FuncCall::class];
    }

    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isName($node, 'mysql_pconnect')) {
            return null;
        }

        $node->name = new Name('mysqli_connect');

        $node->args[0]->value = $this->joinStringWithNode('p:', $node->args[0]->value);

        return $node;
    }

    private function joinStringWithNode(string $string, Expr $expr): Expr
    {
        if ($expr instanceof String_) {
            return new String_($string . $expr->value);
        }

        return new Concat(new String_($string), $expr);
    }
}
