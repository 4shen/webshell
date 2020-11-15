<?php

declare(strict_types=1);

namespace Rector\Php55\NodeFactory;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Parser;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\NodeTraverser\CallableNodeTraverser;

final class AnonymousFunctionNodeFactory
{
    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    public function __construct(Parser $parser, CallableNodeTraverser $callableNodeTraverser)
    {
        $this->parser = $parser;
        $this->callableNodeTraverser = $callableNodeTraverser;
    }

    public function createAnonymousFunctionFromString(Expr $expr): ?Closure
    {
        if (! $expr instanceof String_) {
            // not supported yet
            throw new ShouldNotHappenException();
        }

        $phpCode = '<?php ' . $expr->value . ';';
        $contentNodes = $this->parser->parse($phpCode);

        $anonymousFunction = new Closure();
        if (! $contentNodes[0] instanceof Expression) {
            return null;
        }

        $stmt = $contentNodes[0]->expr;

        $this->callableNodeTraverser->traverseNodesWithCallable($stmt, function (Node $node): Node {
            if (! $node instanceof String_) {
                return $node;
            }

            $match = Strings::match($node->value, '#(\\$|\\\\|\\x0)(?<number>\d+)#');
            if (! $match) {
                return $node;
            }

            $matchesVariable = new Variable('matches');

            return new ArrayDimFetch($matchesVariable, new LNumber((int) $match['number']));
        });

        $anonymousFunction->stmts[] = new Return_($stmt);
        $anonymousFunction->params[] = new Param(new Variable('matches'));

        return $anonymousFunction;
    }
}
