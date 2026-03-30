<?php

declare(strict_types=1);

namespace App\Actions;

use App\Contracts\StepHandler;
use App\Models\Infrastructure;
use App\Services\BastionSshExecutor;

final readonly class ScpInventoryToBastion implements StepHandler
{
    public function __construct(private BastionSshExecutor $ssh) {}

    public function handle(Infrastructure $infrastructure): void
    {
        $inventoryPath = GenerateInventory::inventoryPath($infrastructure);

        $sshUser = $infrastructure->cloudProvider->type->sshUser();
        $homeDir = $sshUser === 'root' ? '/root' : "/home/{$sshUser}";

        $this->ssh->scp($infrastructure, $inventoryPath, "{$homeDir}/playbooks/inventory/hosts.ini");
    }
}
