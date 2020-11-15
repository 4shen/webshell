<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PHPStan\Type\StringType;
use Rector\Core\Rector\AbstractPHPUnitRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see https://github.com/sebastianbergmann/phpunit/blob/master/ChangeLog-8.0.md
 * @see \Rector\PHPUnit\Tests\Rector\MethodCall\SpecificAssertContainsRector\SpecificAssertContainsRectorTest
 */
final class SpecificAssertContainsRector extends AbstractPHPUnitRector
{
    /**
     * @var string[][]
     */
    private const OLD_METHODS_NAMES_TO_NEW_NAMES = [
        'string' => [
            'assertContains' => 'assertStringContainsString',
            'assertNotContains' => 'assertStringNotContainsString',
        ],
    ];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Change assertContains()/assertNotContains() method to new string and iterable alternatives',
            [
                new CodeSample(
                    <<<'PHP'
<?php

final class SomeTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertContains('foo', 'foo bar');
        $this->assertNotContains('foo', 'foo bar');
    }
}
PHP
                    ,
                    <<<'PHP'
<?php

final class SomeTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->assertStringContainsString('foo', 'foo bar');
        $this->assertStringNotContainsString('foo', 'foo bar');
    }
}
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
        return [MethodCall::class, StaticCall::class];
    }

    /**
     * @param MethodCall|StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isPHPUnitMethodNames($node, ['assertContains', 'assertNotContains'])) {
            return null;
        }

        if (! $this->isStaticType($node->args[1]->value, StringType::class)) {
            return null;
        }

        $methodName = $this->getName($node->name);

        $node->name = new Identifier(self::OLD_METHODS_NAMES_TO_NEW_NAMES['string'][$methodName]);

        return $node;
    }
}
