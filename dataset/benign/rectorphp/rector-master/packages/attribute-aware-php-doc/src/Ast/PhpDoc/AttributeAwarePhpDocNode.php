<?php

declare(strict_types=1);

namespace Rector\AttributeAwarePhpDoc\Ast\PhpDoc;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocChildNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use Rector\BetterPhpDocParser\Attributes\Attribute\AttributeTrait;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface;

final class AttributeAwarePhpDocNode extends PhpDocNode implements AttributeAwareNodeInterface
{
    use AttributeTrait;

    /**
     * @var PhpDocChildNode[]|AttributeAwareNodeInterface[]
     */
    public $children = [];

    public function __toString(): string
    {
        return "/**\n * " . implode("\n * ", $this->children) . "\n */";
    }
}
