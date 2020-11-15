<?php

declare(strict_types=1);

namespace Rector\Php71\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Cast\Array_ as ArrayCast;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\PropertyProperty;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\ErrorType;
use PHPStan\Type\MixedType;
use PHPStan\Type\StringType;
use PHPStan\Type\UnionType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see https://3v4l.org/ABDNv
 * @see https://stackoverflow.com/a/41000866/1348344
 * @see \Rector\Php71\Tests\Rector\Assign\AssignArrayToStringRector\AssignArrayToStringRectorTest
 */
final class AssignArrayToStringRector extends AbstractRector
{
    /**
     * @var PropertyProperty[]
     */
    private $emptyStringPropertyNodes = [];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'String cannot be turned into array by assignment anymore',
            [new CodeSample(<<<'PHP'
$string = '';
$string[] = 1;
PHP
                , <<<'PHP'
$string = [];
$string[] = 1;
PHP
            )]
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
        // only array with no explicit key assign, e.g. "$value[] = 5";
        if (! $node->var instanceof ArrayDimFetch || $node->var->dim !== null) {
            return null;
        }

        $arrayDimFetchNode = $node->var;

        /** @var Variable|PropertyFetch|StaticPropertyFetch|Expr $variableNode */
        $variableNode = $arrayDimFetchNode->var;

        // set default value to property
        if (($variableNode instanceof PropertyFetch || $variableNode instanceof StaticPropertyFetch) &&
            $this->processProperty($variableNode)
        ) {
            return $node;
        }

        // fallback to variable, property or static property = '' set
        if ($this->processVariable($node, $variableNode)) {
            return $node;
        }

        // there is "$string[] = ...;", which would cause error in PHP 7+
        // fallback - if no array init found, retype to (array)
        $retypeArrayAssignNode = new Assign($arrayDimFetchNode->var, new ArrayCast($arrayDimFetchNode->var));

        $this->addNodeAfterNode(clone $node, $node);

        return $retypeArrayAssignNode;
    }

    /**
     * @param Node[] $nodes
     * @return Node[]|null
     */
    public function beforeTraverse(array $nodes): ?array
    {
        // collect all known "{anything} = '';" assigns
        $this->traverseNodesWithCallable($nodes, function (Node $node): void {
            if ($node instanceof PropertyProperty && $node->default && $this->isEmptyStringNode($node->default)) {
                $this->emptyStringPropertyNodes[] = $node;
            }
        });

        return $nodes;
    }

    /**
     * @param PropertyFetch|StaticPropertyFetch $propertyNode
     */
    private function processProperty(Node $propertyNode): bool
    {
        foreach ($this->emptyStringPropertyNodes as $emptyStringPropertyNode) {
            if ($this->areNamesEqual($emptyStringPropertyNode, $propertyNode)) {
                $emptyStringPropertyNode->default = new Array_();

                return true;
            }
        }

        return false;
    }

    /**
     * @param Variable|PropertyFetch|StaticPropertyFetch|Expr $expr
     */
    private function processVariable(Assign $assign, Expr $expr): bool
    {
        if ($this->shouldSkipVariable($expr)) {
            return true;
        }

        $variableAssign = $this->betterNodeFinder->findFirstPrevious($assign, function (Node $node) use ($expr): bool {
            if (! $node instanceof Assign) {
                return false;
            }

            if (! $this->areNodesEqual($node->var, $expr)) {
                return false;
            }
            // we look for variable assign = string
            return $this->isEmptyStringNode($node->expr);
        });

        if ($variableAssign instanceof Assign) {
            $variableAssign->expr = new Array_();
            return true;
        }

        return false;
    }

    private function isEmptyStringNode(Node $node): bool
    {
        return $node instanceof String_ && $node->value === '';
    }

    private function shouldSkipVariable(Expr $expr): bool
    {
        $staticType = $this->getStaticType($expr);
        if ($staticType instanceof ErrorType) {
            return false;
        }

        if ($staticType instanceof UnionType) {
            return ! ($staticType->isSuperTypeOf(new ArrayType(new MixedType(), new MixedType()))->yes() &&
                $staticType->isSuperTypeOf(new ConstantStringType(''))->yes());
        }

        return ! $staticType instanceof StringType;
    }
}
