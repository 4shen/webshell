<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer;

use PhpParser\Node\FunctionLike;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\TypeDeclaration\Contract\TypeInferer\ReturnTypeInfererInterface;
use Rector\TypeDeclaration\TypeNormalizer;

final class ReturnTypeInferer extends AbstractPriorityAwareTypeInferer
{
    /**
     * @var ReturnTypeInfererInterface[]
     */
    private $returnTypeInferers = [];

    /**
     * @var TypeNormalizer
     */
    private $typeNormalizer;

    /**
     * @param ReturnTypeInfererInterface[] $returnTypeInferers
     */
    public function __construct(array $returnTypeInferers, TypeNormalizer $typeNormalizer)
    {
        $this->returnTypeInferers = $this->sortTypeInferersByPriority($returnTypeInferers);
        $this->typeNormalizer = $typeNormalizer;
    }

    public function inferFunctionLike(FunctionLike $functionLike): Type
    {
        return $this->inferFunctionLikeWithExcludedInferers($functionLike, []);
    }

    /**
     * @param string[] $excludedInferers
     */
    public function inferFunctionLikeWithExcludedInferers(FunctionLike $functionLike, array $excludedInferers): Type
    {
        foreach ($this->returnTypeInferers as $returnTypeInferer) {
            if ($this->shouldSkipExcludedTypeInferer($returnTypeInferer, $excludedInferers)) {
                continue;
            }

            $type = $returnTypeInferer->inferFunctionLike($functionLike);

            $type = $this->typeNormalizer->normalizeArrayTypeAndArrayNever($type);
            $type = $this->typeNormalizer->uniqueateConstantArrayType($type);
            $type = $this->typeNormalizer->normalizeArrayOfUnionToUnionArray($type);

            // in case of void, check return type of children methods
            if ($type instanceof MixedType) {
                continue;
            }

            return $type;
        }

        return new MixedType();
    }

    /**
     * @param string[] $excludedInferers
     */
    private function shouldSkipExcludedTypeInferer(
        ReturnTypeInfererInterface $returnTypeInferer,
        array $excludedInferers
    ): bool {
        foreach ($excludedInferers as $excludedInferer) {
            $this->ensureIsTypeInferer($excludedInferer);

            if (is_a($returnTypeInferer, $excludedInferer)) {
                return true;
            }
        }

        return false;
    }

    private function ensureIsTypeInferer(string $excludedInferer): void
    {
        if (is_a($excludedInferer, ReturnTypeInfererInterface::class, true)) {
            return;
        }

        throw new ShouldNotHappenException();
    }
}
