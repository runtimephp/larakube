<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\ManagementCluster;
use SensitiveParameter;

final class StoreManagementSshKey
{
    public function handle(ManagementCluster $cluster, #[SensitiveParameter] string $sshPrivateKey): void
    {
        $cluster->update(['ssh_private_key' => $sshPrivateKey]);
    }
}
