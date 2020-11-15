<?php
declare(strict_types=1);

namespace Lcobucci\JWT\Signer\Rsa;

use Lcobucci\JWT\Signer\Rsa;

use const OPENSSL_ALGO_SHA512;

final class Sha512 extends Rsa
{
    public function getAlgorithmId(): string
    {
        return 'RS512';
    }

    public function getAlgorithm(): int
    {
        return OPENSSL_ALGO_SHA512;
    }
}
