<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\ServerRole;

final readonly class CreateServerData
{
    /**
     * @param  list<int|string>  $sshKeyIds
     */
    public function __construct(
        public string $name,
        public string $type,
        public string $image,
        public string $region,
        public string $infrastructure_id,
        public ?ServerRole $role = null,
        public ?int $cpus = null,
        public ?string $memory = null,
        public ?string $disk = null,
        public array $sshKeyIds = [],
        public ?string $cloudInit = null,
    ) {}
}
