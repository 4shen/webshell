<?php

declare(strict_types=1);

namespace Rector\Nette\Rector\FuncCall;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\Minus;
use PhpParser\Node\Expr\Cast\Bool_;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\LNumber;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see https://www.tomasvotruba.cz/blog/2019/02/07/what-i-learned-by-using-thecodingmachine-safe/#is-there-a-better-way
 *
 * @see \Rector\Nette\Tests\Rector\FuncCall\PregMatchFunctionToNetteUtilsStringsRector\PregMatchFunctionToNetteUtilsStringsRectorTest
 */
final class PregMatchFunctionToNetteUtilsStringsRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private const FUNCTION_NAME_TO_METHOD_NAME = [
        'preg_match' => 'match',
        'preg_match_all' => 'matchAll',
    ];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Use Nette\Utils\Strings over bare preg_match() and preg_match_all() functions', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        $content = 'Hi my name is Tom';
        preg_match('#Hi#', $content, $matches);
    }
}
PHP
                ,
                <<<'PHP'
use Nette\Utils\Strings;

class SomeClass
{
    public function run()
    {
        $content = 'Hi my name is Tom';
        $matches = Strings::match($content, '#Hi#');
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
        return [FuncCall::class, Identical::class];
    }

    /**
     * @param FuncCall|Identical $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Identical) {
            return $this->refactorIdentical($node);
        }

        return $this->refactorFuncCall($node);
    }

    private function refactorIdentical(Identical $identical): ?Bool_
    {
        $parentNode = $identical->getAttribute(AttributeKey::PARENT_NODE);

        if ($identical->left instanceof FuncCall) {
            $refactoredFuncCall = $this->refactorFuncCall($identical->left);
            if ($refactoredFuncCall !== null && $this->isValue($identical->right, 1)) {
                return $this->createBoolCast($parentNode, $refactoredFuncCall);
            }
        }

        if ($identical->right instanceof FuncCall) {
            $refactoredFuncCall = $this->refactorFuncCall($identical->right);
            if ($refactoredFuncCall !== null && $this->isValue($identical->left, 1)) {
                return $this->createBoolCast($parentNode, $refactoredFuncCall);
            }
        }

        return null;
    }

    /**
     * @return FuncCall|StaticCall|Assign|null
     */
    private function refactorFuncCall(FuncCall $funcCall): ?Expr
    {
        if (! $this->isNames($funcCall, array_keys(self::FUNCTION_NAME_TO_METHOD_NAME))) {
            return null;
        }

        $currentFunctionName = $this->getName($funcCall);

        $methodName = self::FUNCTION_NAME_TO_METHOD_NAME[$currentFunctionName];
        $matchStaticCall = $this->createMatchStaticCall($funcCall, $methodName);

        // skip assigns, might be used with different return value
        $parentNode = $funcCall->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode instanceof Assign) {
            if ($methodName === 'matchAll') {
                // use count
                return new FuncCall(new Name('count'), [new Arg($matchStaticCall)]);
            }

            return null;
        }

        // assign
        if (isset($funcCall->args[2])) {
            return new Assign($funcCall->args[2]->value, $matchStaticCall);
        }

        return $matchStaticCall;
    }

    private function createMatchStaticCall(FuncCall $funcCall, string $methodName): StaticCall
    {
        $args = [];
        $args[] = $funcCall->args[1];
        $args[] = $funcCall->args[0];

        $args = $this->compensateMatchAllEnforcedFlag($methodName, $funcCall, $args);

        return $this->createStaticCall('Nette\Utils\Strings', $methodName, $args);
    }

    /**
     * Compensate enforced flag https://github.com/nette/utils/blob/e3dd1853f56ee9a68bfbb2e011691283c2ed420d/src/Utils/Strings.php#L487
     * See https://stackoverflow.com/a/61424319/1348344
     *
     * @param Arg[] $args
     * @return Arg[]
     */
    private function compensateMatchAllEnforcedFlag(string $methodName, FuncCall $funcCall, array $args): array
    {
        if ($methodName !== 'matchAll') {
            return $args;
        }

        if (count($funcCall->args) !== 3) {
            return $args;
        }

        $args[] = new Arg(new Minus(new ConstFetch(new Name('PREG_SET_ORDER')), new LNumber(1)));

        return $args;
    }
}
