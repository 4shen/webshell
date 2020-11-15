<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_;

use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;
use Rector\PhpAttribute\Contract\PhpAttributableTagNodeInterface;
use Rector\PhpAttribute\PhpDocNode\PhpAttributePhpDocNodePrintTrait;

final class ColumnTagValueNode extends AbstractDoctrineTagValueNode implements PhpAttributableTagNodeInterface
{
    use PhpAttributePhpDocNodePrintTrait;

    public function changeType(string $type): void
    {
        $this->items['type'] = $type;
    }

    public function getType(): ?string
    {
        return $this->items['type'];
    }

    public function isNullable(): ?bool
    {
        return $this->items['nullable'];
    }

    public function getShortName(): string
    {
        return '@ORM\Column';
    }

    public function toAttributeString(): string
    {
        $items = $this->createAttributeItems();
        return $this->printItemsToAttributeString($items);
    }

    private function createAttributeItems(): array
    {
        $items = $this->items;

        foreach ($items as $key => $value) {
            if ($key !== 'unique') {
                continue;
            }

            if ($value !== true) {
                continue;
            }

            $items[$key] = 'ORM\Column::UNIQUE';
        }

        return $items;
    }
}
