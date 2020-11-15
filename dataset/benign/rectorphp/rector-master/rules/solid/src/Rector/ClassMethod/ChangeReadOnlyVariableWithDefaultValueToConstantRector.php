<?php

declare(strict_types=1);

namespace Rector\SOLID\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Const_;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\MixedType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\Manipulator\ClassMethodAssignManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\Util\StaticRectorStrings;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\SOLID\Tests\Rector\ClassMethod\ChangeReadOnlyVariableWithDefaultValueToConstantRector\ChangeReadOnlyVariableWithDefaultValueToConstantRectorTest
 */
final class ChangeReadOnlyVariableWithDefaultValueToConstantRector extends AbstractRector
{
    /**
     * @var ClassMethodAssignManipulator
     */
    private $classMethodAssignManipulator;

    public function __construct(ClassMethodAssignManipulator $classMethodAssignManipulator)
    {
        $this->classMethodAssignManipulator = $classMethodAssignManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change variable with read only status with default value to constant', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        $replacements = [
            'PHPUnit\Framework\TestCase\Notice' => 'expectNotice',
            'PHPUnit\Framework\TestCase\Deprecated' => 'expectDeprecation',
        ];

        foreach ($replacements as $class => $method) {
        }
    }
}
PHP
,
                <<<'PHP'
class SomeClass
{
    /**
     * @var string[]
     */
    private const REPLACEMENTS = [
        'PHPUnit\Framework\TestCase\Notice' => 'expectNotice',
        'PHPUnit\Framework\TestCase\Deprecated' => 'expectDeprecation',
    ];

    public function run()
    {
        foreach (self::REPLACEMENTS as $class => $method) {
        }
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
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $readOnlyVariableAssigns = $this->collectReadOnlyVariableAssigns($node);
        $readOnlyVariableAssigns = $this->filterOutUniqueNames($readOnlyVariableAssigns);

        if ($readOnlyVariableAssigns === []) {
            return null;
        }

        foreach ($readOnlyVariableAssigns as $readOnlyVariable) {
            $methodName = $readOnlyVariable->getAttribute(AttributeKey::METHOD_NAME);
            if (! is_string($methodName)) {
                throw new ShouldNotHappenException();
            }

            $classMethod = $node->getMethod($methodName);
            if ($classMethod === null) {
                throw new ShouldNotHappenException();
            }

            $this->refactorClassMethod($classMethod, $node, $readOnlyVariableAssigns);
        }

        return $node;
    }

    /**
     * @return Assign[]
     */
    private function collectReadOnlyVariableAssigns(Class_ $class): array
    {
        $readOnlyVariables = [];

        foreach ($class->getMethods() as $classMethod) {
            $readOnlyVariableAssignScalarVariables = $this->classMethodAssignManipulator->collectReadyOnlyAssignScalarVariables(
                $classMethod
            );

            $readOnlyVariables = array_merge($readOnlyVariables, $readOnlyVariableAssignScalarVariables);
        }

        return $readOnlyVariables;
    }

    /**
     * @param Assign[] $assigns
     * @return Assign[]
     */
    private function filterOutUniqueNames(array $assigns): array
    {
        $assignsByName = [];
        foreach ($assigns as $assign) {
            /** @var string $variableName */
            $variableName = $this->getName($assign->var);

            $assignsByName[$variableName][] = $assign;
        }

        $assignsWithUniqueName = [];
        foreach ($assignsByName as $assigns) {
            if (count($assigns) > 1) {
                continue;
            }

            $assignsWithUniqueName = array_merge($assignsWithUniqueName, $assigns);
        }

        return $assignsWithUniqueName;
    }

    /**
     * @param Assign[] $readOnlyVariableAssigns
     */
    private function refactorClassMethod(ClassMethod $classMethod, Class_ $class, array $readOnlyVariableAssigns): void
    {
        foreach ($readOnlyVariableAssigns as $readOnlyVariableAssign) {
            $this->removeNode($readOnlyVariableAssign);

            /** @var Variable|ClassConstFetch $variable */
            $variable = $readOnlyVariableAssign->var;
            // already overriden
            if (! $variable instanceof Variable) {
                continue;
            }

            $classConst = $this->createClassConst($variable, $readOnlyVariableAssign->expr);

            // replace $variable usage in the code with constant
            $this->addConstantToClass($class, $classConst);

            $variableName = $this->getName($variable);
            if ($variableName === null) {
                throw new ShouldNotHappenException();
            }

            $this->replaceVariableWithClassConstFetch($classMethod, $variableName, $classConst);
        }
    }

    private function createConstantNameFromVariable(Variable $variable): string
    {
        $variableName = $this->getName($variable);
        if ($variableName === null) {
            throw new ShouldNotHappenException();
        }

        $constantName = StaticRectorStrings::camelCaseToUnderscore($variableName);

        return strtoupper($constantName);
    }

    private function decorateWithVarAnnotation(ClassConst $classConst): void
    {
        $constStaticType = $this->getStaticType($classConst->consts[0]->value);
        if ($constStaticType instanceof MixedType) {
            return;
        }

        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $classConst->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            $phpDocInfo = $this->phpDocInfoFactory->createEmpty($classConst);
        }

        $phpDocInfo->changeVarType($constStaticType);
    }

    private function createClassConst(Variable $variable, Expr $expr): ClassConst
    {
        $constantName = $this->createConstantNameFromVariable($variable);

        $constant = new Const_($constantName, $expr);

        $classConst = new ClassConst([$constant]);
        $classConst->flags = Class_::MODIFIER_PRIVATE;

        $this->mirrorComments($classConst, $variable);

        $this->decorateWithVarAnnotation($classConst);

        return $classConst;
    }

    private function replaceVariableWithClassConstFetch(
        ClassMethod $classMethod,
        string $variableName,
        ClassConst $classConst
    ): void {
        $constantName = $this->getName($classConst);
        if ($constantName === null) {
            throw new ShouldNotHappenException();
        }

        $this->traverseNodesWithCallable($classMethod, function (Node $node) use ($variableName, $constantName) {
            if (! $this->isVariableName($node, $variableName)) {
                return null;
            }

            // replace with constant fetch
            $classConstFetch = new ClassConstFetch(new Name('self'), new Identifier($constantName));

            // needed later
            $classConstFetch->setAttribute(AttributeKey::CLASS_NAME, $node->getAttribute(AttributeKey::CLASS_NAME));

            return $classConstFetch;
        });
    }
}
