<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\ServerStatus;

final readonly class ServerData
{
    public function __construct(
        public int|string $externalId,
        public string $name,
        public ServerStatus $status,
        public string $type,
        public string $region,
        public ?string $ipv4 = null,
        public ?string $ipv6 = null,
    ) {}
}
