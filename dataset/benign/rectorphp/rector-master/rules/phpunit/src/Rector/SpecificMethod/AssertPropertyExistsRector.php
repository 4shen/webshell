<?php

declare(strict_types=1);

namespace Rector\PHPUnit\Rector\SpecificMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use Rector\Core\PhpParser\Node\Manipulator\IdentifierManipulator;
use Rector\Core\Rector\AbstractPHPUnitRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\PHPUnit\Tests\Rector\SpecificMethod\AssertPropertyExistsRector\AssertPropertyExistsRectorTest
 */
final class AssertPropertyExistsRector extends AbstractPHPUnitRector
{
    /**
     * @var string[]
     */
    private const RENAME_METHODS_WITH_OBJECT_MAP = [
        'assertTrue' => 'assertObjectHasAttribute',
        'assertFalse' => 'assertObjectNotHasAttribute',
    ];

    /**
     * @var string[]
     */
    private const RENAME_METHODS_WITH_CLASS_MAP = [
        'assertTrue' => 'assertClassHasAttribute',
        'assertFalse' => 'assertClassNotHasAttribute',
    ];

    /**
     * @var IdentifierManipulator
     */
    private $identifierManipulator;

    public function __construct(IdentifierManipulator $identifierManipulator)
    {
        $this->identifierManipulator = $identifierManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Turns `property_exists` comparisons to their method name alternatives in PHPUnit TestCase',
            [
                new CodeSample(
                    '$this->assertTrue(property_exists(new Class, "property"), "message");',
                    '$this->assertClassHasAttribute("property", "Class", "message");'
                ),
                new CodeSample(
                    '$this->assertFalse(property_exists(new Class, "property"), "message");',
                    '$this->assertClassNotHasAttribute("property", "Class", "message");'
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
        if (! $this->isPHPUnitMethodNames($node, ['assertTrue', 'assertFalse'])) {
            return null;
        }

        $firstArgumentValue = $node->args[0]->value;
        if ($firstArgumentValue instanceof StaticCall) {
            return null;
        }
        if (! $this->isName($firstArgumentValue, 'property_exists')) {
            return null;
        }

        $propertyExistsMethodCall = $node->args[0]->value;
        if (! $propertyExistsMethodCall instanceof FuncCall) {
            return null;
        }

        [$firstArgument, $secondArgument] = $propertyExistsMethodCall->args;

        if ($firstArgument->value instanceof Variable) {
            $secondArg = new Variable($firstArgument->value->name);
            $map = self::RENAME_METHODS_WITH_OBJECT_MAP;
        } elseif ($firstArgument->value instanceof New_) {
            $secondArg = $this->getName($firstArgument->value->class);
            $map = self::RENAME_METHODS_WITH_CLASS_MAP;
        } else {
            return null;
        }

        if (! $secondArgument->value instanceof String_) {
            return null;
        }

        unset($node->args[0]);

        $node->args = array_merge($this->createArgs([$secondArgument->value->value, $secondArg]), $node->args);

        $this->identifierManipulator->renameNodeWithMap($node, $map);

        return $node;
    }
}
