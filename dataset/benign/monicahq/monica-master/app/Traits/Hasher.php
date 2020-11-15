<?php

namespace App\Traits;

use App\Services\Instance\IdHasher;
use Illuminate\Database\Eloquent\Model;

trait Hasher
{
    public function getRouteKey()
    {
        return app(IdHasher::class)->encodeId(parent::getRouteKey());
    }

    public function resolveRouteBinding($value, $field = null): ?Model
    {
        $id = $this->decodeId($value);

        return parent::resolveRouteBinding($id, $field);
    }

    protected function decodeId($value)
    {
        return app(IdHasher::class)->decodeId($value);
    }

    public function hashID()
    {
        return $this->getRouteKey();
    }
}
