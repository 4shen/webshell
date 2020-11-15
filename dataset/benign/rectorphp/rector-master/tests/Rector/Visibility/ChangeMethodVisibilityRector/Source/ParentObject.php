<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Rector\Visibility\ChangeMethodVisibilityRector\Source;

class ParentObject
{
    private function toBePublicMethod() {

    }

    static function toBePublicStaticMethod() {

    }

    protected function toBeProtectedMethod() {

    }
    private function toBePrivateMethod() {

    }
}
