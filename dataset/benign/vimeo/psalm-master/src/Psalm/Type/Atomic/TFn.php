<?php
namespace Psalm\Type\Atomic;

/**
 * Represents a closure where we know the return type and params
 */
class TFn extends TNamedObject
{
    use CallableTrait;

    /** @var array<string, bool> */
    public $byref_uses = [];

    /**
     * @return bool
     */
    public function canBeFullyExpressedInPhp()
    {
        return false;
    }
}
