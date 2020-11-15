<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\TypeDeclaration\TypeInferer\ParamTypeInferer;

/**
 * @sponsor Thanks https://spaceflow.io/ for sponsoring this rule - visit them on https://github.com/SpaceFlow-app
 *
 * @see \Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayParamDocTypeRector\AddArrayParamDocTypeRectorTest
 */
final class AddArrayParamDocTypeRector extends AbstractRector
{
    /**
     * @var ParamTypeInferer
     */
    private $paramTypeInferer;

    public function __construct(ParamTypeInferer $paramTypeInferer)
    {
        $this->paramTypeInferer = $paramTypeInferer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Adds @param annotation to array parameters inferred from the rest of the code', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    /**
     * @var int[]
     */
    private $values;

    public function __construct(array $values)
    {
        $this->values = $values;
    }
}
PHP
                ,
                <<<'PHP'
class SomeClass
{
    /**
     * @var int[]
     */
    private $values;

    /**
     * @param int[] $values
     */
    public function __construct(array $values)
    {
        $this->values = $values;
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
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if (count($node->getParams()) === 0) {
            return null;
        }

        /** @var PhpDocInfo $phpDocInfo */
        $phpDocInfo = $node->getAttribute(AttributeKey::PHP_DOC_INFO);

        foreach ($node->getParams() as $param) {
            if ($this->shouldSkipParam($param)) {
                return null;
            }

            $type = $this->paramTypeInferer->inferParam($param);
            if ($type instanceof MixedType) {
                return null;
            }

            $paramName = $this->getName($param);
            $phpDocInfo->changeParamType($type, $param, $paramName);
        }

        return $node;
    }

    private function shouldSkipParam(Param $param): bool
    {
        // type missing at all
        if ($param->type === null) {
            return true;
        }

        // not an array type
        if (! $this->isName($param->type, 'array')) {
            return true;
        }

        // not an array type
        $paramStaticType = $this->getStaticType($param);
        if (! $paramStaticType instanceof ArrayType) {
            return true;
        }

        // is unknown type?
        if (! $paramStaticType->getIterableValueType() instanceof MixedType) {
            return true;
        }

        // is defined mixed[] explicitly
        /** @var MixedType $mixedType */
        $mixedType = $paramStaticType->getIterableValueType();

        return $mixedType->isExplicitMixed();
    }
}
