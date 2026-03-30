<?php

declare(strict_types=1);

namespace App\Actions;

use App\Contracts\StepHandler;
use App\Models\Infrastructure;
use App\Services\BastionSshExecutor;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

final readonly class RunAnsible implements StepHandler
{
    public function __construct(private BastionSshExecutor $ssh) {}

    public function handle(Infrastructure $infrastructure): void
    {
        $sshUser = $infrastructure->cloudProvider->type->sshUser();
        $homeDir = $sshUser === 'root' ? '/root' : "/home/{$sshUser}";

        $logPath = "ansible/{$infrastructure->id}.log";

        Log::info("Running Ansible for infrastructure [{$infrastructure->name}]");

        try {
            $output = $this->ssh->execute(
                $infrastructure,
                "cd {$homeDir}/playbooks && ansible-playbook -i inventory/hosts.ini site.yml 2>&1",
                timeout: 1800,
            );

            Storage::disk('local')->put($logPath, $output);

            Log::info("Ansible completed for infrastructure [{$infrastructure->name}]");
        } catch (Throwable $e) {
            Storage::disk('local')->put($logPath, $e->getMessage());

            Log::error("Ansible failed for infrastructure [{$infrastructure->name}]: {$e->getMessage()}");

            throw $e;
        }
    }
}
