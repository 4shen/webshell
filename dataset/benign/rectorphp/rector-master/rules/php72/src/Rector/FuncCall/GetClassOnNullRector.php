<?php

declare(strict_types=1);

namespace Rector\Php72\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Trait_;
use PHPStan\Analyser\Scope;
use PHPStan\Type\NullType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see http://php.net/manual/en/migration72.incompatible.php#migration72.incompatible.no-null-to-get_class
 * @see https://3v4l.org/sk0fp
 * @see \Rector\Php72\Tests\Rector\FuncCall\GetClassOnNullRector\GetClassOnNullRectorTest
 */
final class GetClassOnNullRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Null is no more allowed in get_class()', [
            new CodeSample(
                <<<'PHP'
final class SomeClass
{
    public function getItem()
    {
        $value = null;
        return get_class($value);
    }
}
PHP
                ,
                <<<'PHP'
final class SomeClass
{
    public function getItem()
    {
        $value = null;
        return $value !== null ? get_class($value) : self::class;
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
        if (! $this->isName($node, 'get_class')) {
            return null;
        }

        // only relevant inside the class
        /** @var Scope|null $nodeScope */
        $nodeScope = $node->getAttribute(AttributeKey::SCOPE);
        if ($nodeScope instanceof Scope && ! $nodeScope->isInClass()) {
            return null;
        }

        // possibly already changed
        if ($this->shouldSkip($node)) {
            return null;
        }

        if (! isset($node->args[0])) {
            return null;
        }

        $valueNode = $node->args[0]->value;
        if (! $this->isNullableType($valueNode) && ! $this->isStaticType($valueNode, NullType::class)) {
            return null;
        }

        $condition = new NotIdentical($valueNode, $this->createNull());

        $newFuncCallNode = new FuncCall($node->name, $node->args);
        $ternaryNode = new Ternary($condition, $newFuncCallNode, new ClassConstFetch(new Name('self'), new Identifier(
            'class'
        )));

        $newFuncCallNode->setAttribute(AttributeKey::PARENT_NODE, $ternaryNode);

        return $ternaryNode;
    }

    private function shouldSkip(FuncCall $funcCall): bool
    {
        $class = $funcCall->getAttribute(AttributeKey::CLASS_NODE);
        if ($class instanceof Trait_) {
            return true;
        }

        $parentNode = $funcCall->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parentNode instanceof Ternary) {
            return false;
        }

        if ($this->isIdenticalToNotNull($funcCall, $parentNode)) {
            return true;
        }

        return $this->isNotIdenticalToNull($funcCall, $parentNode);
    }

    /**
     * E.g. "$value === [!null] ? get_class($value)"
     */
    private function isIdenticalToNotNull(FuncCall $funcCall, Ternary $ternary): bool
    {
        if (! $ternary->cond instanceof Identical) {
            return false;
        }

        if ($this->areNodesEqual($ternary->cond->left, $funcCall->args[0]->value) && ! $this->isNull(
            $ternary->cond->right
        )) {
            return true;
        }
        return $this->areNodesEqual($ternary->cond->right, $funcCall->args[0]->value) && ! $this->isNull(
            $ternary->cond->left
        );
    }

    /**
     * E.g. "$value !== null ? get_class($value)"
     */
    private function isNotIdenticalToNull(FuncCall $funcCall, Ternary $ternary): bool
    {
        if (! $ternary->cond instanceof NotIdentical) {
            return false;
        }

        if ($this->areNodesEqual($ternary->cond->left, $funcCall->args[0]->value) && $this->isNull(
            $ternary->cond->right
        )) {
            return true;
        }
        return $this->areNodesEqual($ternary->cond->right, $funcCall->args[0]->value) && $this->isNull(
            $ternary->cond->left
        );
    }
}
