<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\SshKeyPurpose;
use App\Models\Infrastructure;
use App\Models\SshKey;

final readonly class CreateSshKey
{
    public function handle(
        Infrastructure $infrastructure,
        string $name,
        string $fingerprint,
        string $publicKey,
        SshKeyPurpose $purpose,
        ?string $privateKey = null,
    ): SshKey {
        /** @var SshKey */
        return SshKey::query()->create([
            'infrastructure_id' => $infrastructure->id,
            'name' => $name,
            'fingerprint' => $fingerprint,
            'public_key' => $publicKey,
            'purpose' => $purpose,
            'private_key' => $privateKey,
        ]);
    }
}
