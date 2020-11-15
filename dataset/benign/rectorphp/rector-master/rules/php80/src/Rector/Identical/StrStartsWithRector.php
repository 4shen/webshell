<?php

declare(strict_types=1);

namespace Rector\Php80\Rector\Identical;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Php80\Contract\StrStartWithMatchAndRefactorInterface;

/**
 * @see https://wiki.php.net/rfc/add_str_starts_with_and_ends_with_functions
 *
 * @see https://3v4l.org/RQHB5 for weak compare
 * @see https://3v4l.org/AmLja for weak compare
 *
 * @see \Rector\Php80\Tests\Rector\Identical\StrStartsWithRector\StrStartsWithRectorTest
 */
final class StrStartsWithRector extends AbstractRector
{
    /**
     * @var StrStartWithMatchAndRefactorInterface[]
     */
    private $strStartWithMatchAndRefactors = [];

    /**
     * @param StrStartWithMatchAndRefactorInterface[] $strStartWithMatchAndRefactors
     */
    public function __construct(array $strStartWithMatchAndRefactors)
    {
        $this->strStartWithMatchAndRefactors = $strStartWithMatchAndRefactors;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change helper functions to str_starts_with()', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        $isMatch = substr($haystack, 0, strlen($needle)) === $needle;

        $isNotMatch = substr($haystack, 0, strlen($needle)) !== $needle;
    }
}
PHP
,
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        $isMatch = str_starts_with($haystack, $needle);

        $isNotMatch = ! str_starts_with($haystack, $needle);
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
        return [Identical::class, NotIdentical::class];
    }

    /**
     * @param Identical|NotIdentical $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($this->strStartWithMatchAndRefactors as $strStartWithMatchAndRefactor) {
            $strStartsWithValueObject = $strStartWithMatchAndRefactor->match($node);
            if ($strStartsWithValueObject === null) {
                continue;
            }

            return $strStartWithMatchAndRefactor->refactor($strStartsWithValueObject);
        }
    }
}
