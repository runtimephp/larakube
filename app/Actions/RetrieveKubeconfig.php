<?php

declare(strict_types=1);

namespace App\Actions;

use App\Contracts\StepHandler;
use App\Models\Infrastructure;
use App\Services\BastionSshExecutor;
use Illuminate\Support\Facades\Storage;

final readonly class RetrieveKubeconfig implements StepHandler
{
    public function __construct(private BastionSshExecutor $ssh) {}

    public function handle(Infrastructure $infrastructure): void
    {
        $kubeconfig = $this->ssh->execute(
            $infrastructure,
            'cat /root/.kube/config',
        );

        Storage::disk('local')->put("kubeconfigs/{$infrastructure->id}.conf", $kubeconfig);
    }
}
