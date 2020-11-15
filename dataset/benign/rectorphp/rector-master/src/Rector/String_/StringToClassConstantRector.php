<?php

declare(strict_types=1);

namespace Rector\Core\Rector\String_;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Core\Tests\Rector\String_\StringToClassConstantRector\StringToClassConstantRectorTest
 */
final class StringToClassConstantRector extends AbstractRector
{
    /**
     * @var string[][]
     */
    private $stringsToClassConstants = [];

    /**
     * @param string[][] $stringsToClassConstants
     */
    public function __construct(array $stringsToClassConstants = [])
    {
        $this->stringsToClassConstants = $stringsToClassConstants;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Changes strings to specific constants', [
            new ConfiguredCodeSample(
                <<<'PHP'
final class SomeSubscriber
{
    public static function getSubscribedEvents()
    {
        return ['compiler.post_dump' => 'compile'];
    }
}
PHP
                ,
                <<<'PHP'
final class SomeSubscriber
{
    public static function getSubscribedEvents()
    {
        return [\Yet\AnotherClass::CONSTANT => 'compile'];
    }
}
PHP
                ,
                [
                    'compiler.post_dump' => ['Yet\AnotherClass', 'CONSTANT'],
                ]
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [String_::class];
    }

    /**
     * @param String_ $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($this->stringsToClassConstants as $string => $classConstant) {
            if (! $this->isValue($node, $string)) {
                continue;
            }

            return new ClassConstFetch(new FullyQualified($classConstant[0]), $classConstant[1]);
        }

        return $node;
    }
}
