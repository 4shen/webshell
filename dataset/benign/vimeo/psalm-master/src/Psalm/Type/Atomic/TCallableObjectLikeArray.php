<?php
namespace Psalm\Type\Atomic;

/**
 * Represents a non-empty array
 */
class TCallableObjectLikeArray extends ObjectLike
{
    const KEY = 'callable-array';

    public function getKey(bool $include_extra = true)
    {
        return 'array';
    }
}
