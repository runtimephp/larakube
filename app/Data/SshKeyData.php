<?php

declare(strict_types=1);

namespace App\Data;

final readonly class SshKeyData
{
    public function __construct(
        public int|string $externalId,
        public string $name,
        public string $fingerprint,
        public string $publicKey,
    ) {}
}
