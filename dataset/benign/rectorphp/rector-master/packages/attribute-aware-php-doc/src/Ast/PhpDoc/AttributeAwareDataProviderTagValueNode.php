<?php

declare(strict_types=1);

namespace Rector\AttributeAwarePhpDoc\Ast\PhpDoc;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use Rector\BetterPhpDocParser\Attributes\Attribute\AttributeTrait;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface;

final class AttributeAwareDataProviderTagValueNode implements PhpDocTagValueNode, AttributeAwareNodeInterface
{
    use AttributeTrait;

    /**
     * @var string
     */
    private $method;

    public function __construct(string $method)
    {
        $this->method = $method;
    }

    public function __toString(): string
    {
        return $this->method;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function changeMethod(string $method): void
    {
        $this->method = $method;
    }
}
