<?php

declare(strict_types=1);

namespace App\Data;

final readonly class SshKeypairData
{
    public function __construct(
        public string $publicKey,
        public string $privateKey,
    ) {}
}
