<?php

declare(strict_types=1);

namespace App\Actions;

use App\Contracts\StepHandler;
use App\Exceptions\RetryStepException;
use App\Models\Infrastructure;
use App\Services\BastionSshExecutor;
use RuntimeException;

final readonly class RunAnsible implements StepHandler
{
    public function __construct(private BastionSshExecutor $ssh) {}

    public function handle(Infrastructure $infrastructure): void
    {
        $sshUser = $infrastructure->cloudProvider->type->sshUser();
        $homeDir = $sshUser === 'root' ? '/root' : "/home/{$sshUser}";

        // Wait for cloud-init to finish installing Ansible
        $checkOutput = $this->ssh->execute(
            $infrastructure,
            'which ansible-playbook 2>&1 || echo ANSIBLE_NOT_FOUND',
        );

        if (str_contains($checkOutput, 'ANSIBLE_NOT_FOUND')) {
            throw new RetryStepException('Ansible not yet installed on bastion (cloud-init still running).');
        }

        try {
            $this->ssh->execute(
                $infrastructure,
                "cd {$homeDir}/playbooks && ANSIBLE_FORCE_COLOR=false ansible-playbook -i inventory/hosts.ini site.yml 2>&1",
                timeout: 1800,
            );
        } catch (RuntimeException $e) {
            if (str_contains($e->getMessage(), 'unreachable=')) {
                throw new RetryStepException('Ansible failed with unreachable hosts: '.$e->getMessage());
            }

            throw $e;
        }
    }
}
