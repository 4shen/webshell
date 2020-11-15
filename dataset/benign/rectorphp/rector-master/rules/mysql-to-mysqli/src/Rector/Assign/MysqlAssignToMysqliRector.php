<?php

declare(strict_types=1);

namespace Rector\MysqlToMysqli\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Smaller;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\PostInc;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\For_;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see https://www.phpclasses.org/blog/package/9199/post/3-Smoothly-Migrate-your-PHP-Code-using-the-Old-MySQL-extension-to-MySQLi.html
 * @see \Rector\MysqlToMysqli\Tests\Rector\Assign\MysqlAssignToMysqliRector\MysqlAssignToMysqliRectorTest
 */
final class MysqlAssignToMysqliRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private const FIELD_TO_FIELD_DIRECT = [
        'mysql_field_len' => 'length',
        'mysql_field_name' => 'name',
        'mysql_field_table' => 'table',
    ];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Converts more complex mysql functions to mysqli',
            [
                new CodeSample(
                    <<<'PHP'
$data = mysql_db_name($result, $row);
PHP
                    ,
                    <<<'PHP'
mysqli_data_seek($result, $row);
$fetch = mysql_fetch_row($result);
$data = $fetch[0];
PHP
                ),
            ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Assign::class];
    }

    /**
     * @param Assign $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $node->expr instanceof FuncCall) {
            return null;
        }

        /** @var FuncCall $funcCallNode */
        $funcCallNode = $node->expr;

        if ($this->isName($funcCallNode, 'mysql_tablename')) {
            return $this->processMysqlTableName($node, $funcCallNode);
        }

        if ($this->isName($funcCallNode, 'mysql_db_name')) {
            return $this->processMysqlDbName($node, $funcCallNode);
        }

        if ($this->isName($funcCallNode, 'mysql_db_query')) {
            return $this->processMysqliSelectDb($node, $funcCallNode);
        }

        if ($this->isName($funcCallNode, 'mysql_fetch_field')) {
            return $this->processMysqlFetchField($node, $funcCallNode);
        }

        return $this->processFieldToFieldDirect($node, $funcCallNode);
    }

    private function processMysqlTableName(Assign $assign, FuncCall $funcCall): FuncCall
    {
        $funcCall->name = new Name('mysqli_data_seek');

        $newFuncCall = new FuncCall(new Name('mysql_fetch_array'), [$funcCall->args[0]]);
        $newAssignNode = new Assign($assign->var, new ArrayDimFetch($newFuncCall, new LNumber(0)));

        $this->addNodeAfterNode($newAssignNode, $assign);

        return $funcCall;
    }

    private function processMysqlDbName(Assign $assign, FuncCall $funcCall): FuncCall
    {
        $funcCall->name = new Name('mysqli_data_seek');

        $mysqlFetchRowFuncCall = new FuncCall(new Name('mysql_fetch_row'), [$funcCall->args[0]]);
        $fetchVariable = new Variable('fetch');
        $newAssignNode = new Assign($fetchVariable, $mysqlFetchRowFuncCall);
        $this->addNodeAfterNode($newAssignNode, $assign);

        $newAssignNode = new Assign($assign->var, new ArrayDimFetch($fetchVariable, new LNumber(0)));
        $this->addNodeAfterNode($newAssignNode, $assign);

        return $funcCall;
    }

    private function processMysqliSelectDb(Assign $assign, FuncCall $funcCall): FuncCall
    {
        $funcCall->name = new Name('mysqli_select_db');

        $newAssignNode = new Assign($assign->var, new FuncCall(new Name('mysqli_query'), [$funcCall->args[1]]));
        $this->addNodeAfterNode($newAssignNode, $assign);

        unset($funcCall->args[1]);

        return $funcCall;
    }

    private function processMysqlFetchField(Assign $assign, FuncCall $funcCall): Assign
    {
        $funcCall->name = new Name('mysqli_fetch_field');

        if (! isset($funcCall->args[1])) {
            return $assign;
        }

        unset($funcCall->args[1]);

        // add for
        $xVar = new Variable('x');
        $forNode = new For_([
            'init' => [new Assign($xVar, new LNumber(0))],
            'cond' => [new Smaller($xVar, new LNumber(5))],
            'loop' => [new PostInc($xVar)],
            'stmts' => [new Expression($funcCall)],
        ]);

        $previousStatement = $assign->getAttribute(AttributeKey::PREVIOUS_STATEMENT);
        if ($previousStatement === null) {
            throw new ShouldNotHappenException();
        }

        $this->addNodeAfterNode($forNode, $previousStatement);

        return $assign;
    }

    private function processFieldToFieldDirect(Assign $assign, FuncCall $funcCall): ?Assign
    {
        foreach (self::FIELD_TO_FIELD_DIRECT as $funcName => $property) {
            if ($this->isName($funcCall, $funcName)) {
                $parentNode = $funcCall->getAttribute(AttributeKey::PARENT_NODE);
                if ($parentNode instanceof PropertyFetch || $parentNode instanceof StaticPropertyFetch) {
                    continue;
                }

                $funcCall->name = new Name('mysqli_fetch_field_direct');
                $assign->expr = new PropertyFetch($funcCall, $property);

                return $assign;
            }
        }

        return null;
    }
}
