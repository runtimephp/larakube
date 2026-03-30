<?php

declare(strict_types=1);

namespace App\Actions;

use App\Contracts\StepHandler;
use App\Models\Infrastructure;
use App\Services\BastionSshExecutor;

final readonly class RunAnsible implements StepHandler
{
    public function __construct(private BastionSshExecutor $ssh) {}

    public function handle(Infrastructure $infrastructure): void
    {
        $sshUser = $infrastructure->cloudProvider->type->sshUser();
        $homeDir = $sshUser === 'root' ? '/root' : "/home/{$sshUser}";

        $this->ssh->execute(
            $infrastructure,
            "cd {$homeDir}/playbooks && ansible-playbook -i inventory/hosts.ini site.yml 2>&1",
            timeout: 1800,
        );
    }
}
