<?php

declare(strict_types=1);

namespace Rector\Core\Tests\PhpParser\Printer;

use Iterator;
use PhpParser\Builder\Method;
use PhpParser\Comment;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Expr\Yield_;
use PhpParser\Node\Scalar\String_;
use Rector\Core\HttpKernel\RectorKernel;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\PackageBuilder\Tests\AbstractKernelTestCase;

final class BetterStandardPrinterTest extends AbstractKernelTestCase
{
    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    protected function setUp(): void
    {
        $this->bootKernel(RectorKernel::class);
        $this->betterStandardPrinter = self::$container->get(BetterStandardPrinter::class);
    }

    public function testAddingCommentOnSomeNodesFail(): void
    {
        $methodCall = new MethodCall(new Variable('this'), 'run');
        $methodCall->setAttribute(AttributeKey::COMMENTS, [new Comment('// todo: fix')]);

        $methodBuilder = new Method('run');
        $methodBuilder->addStmt($methodCall);

        $classMethod = $methodBuilder->getNode();

        $printed = $this->betterStandardPrinter->print($classMethod) . PHP_EOL;
        $this->assertStringEqualsFile(
            __DIR__ . '/Source/expected_code_with_non_stmt_placed_nested_comment.php.inc',
            $printed
        );
    }

    public function testStringWithAddedComment(): void
    {
        $string = new String_('hey');
        $string->setAttribute(AttributeKey::COMMENTS, [new Comment('// todo: fix')]);

        $printed = $this->betterStandardPrinter->print($string) . PHP_EOL;
        $this->assertStringEqualsFile(__DIR__ . '/Source/expected_code_with_comment.php.inc', $printed);
    }

    /**
     * @dataProvider provideDataForDoubleSlashEscaping()
     */
    public function testDoubleSlashEscaping(string $content, string $expectedOutput): void
    {
        $printed = $this->betterStandardPrinter->print(new String_($content));
        $this->assertSame($expectedOutput, $printed);
    }

    public function provideDataForDoubleSlashEscaping(): Iterator
    {
        yield ['Vendor\Name', "'Vendor\Name'"];
        yield ['Vendor\\', "'Vendor\\\\'"];
        yield ["Vendor'Name", "'Vendor\'Name'"];
    }

    public function testYield(): void
    {
        $printed = $this->betterStandardPrinter->print(new Yield_(new String_('value')));
        $this->assertSame("yield 'value'", $printed);

        $printed = $this->betterStandardPrinter->print(new Yield_());
        $this->assertSame('yield', $printed);
    }
}
