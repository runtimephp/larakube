<?php

declare(strict_types=1);

namespace App\Actions;

use App\Contracts\StepHandler;
use App\Enums\ServerRole;
use App\Models\Infrastructure;
use App\Models\Server;
use App\Queries\ServerQuery;
use Illuminate\Support\Facades\Storage;

final readonly class GenerateInventory implements StepHandler
{
    public function __construct(private ServerQuery $serverQuery) {}

    public static function inventoryPath(Infrastructure $infrastructure): string
    {
        return Storage::disk('local')->path("inventories/{$infrastructure->id}.ini");
    }

    public function handle(Infrastructure $infrastructure): void
    {
        $servers = ($this->serverQuery)()
            ->byInfrastructure($infrastructure)
            ->get();

        $sshUser = $infrastructure->cloudProvider->type->sshUser();

        $bastion = $servers->firstWhere('role', ServerRole::Bastion);
        $controlPlanes = $servers->where('role', ServerRole::ControlPlane);
        $workers = $servers->where('role', ServerRole::Node);

        $lines = [];

        $lines[] = '[bastion]';
        if ($bastion instanceof Server) {
            $lines[] = "{$bastion->name} ansible_host={$bastion->ipv4}";
        }
        $lines[] = '';

        $lines[] = '[controlplane]';
        foreach ($controlPlanes as $cp) {
            $lines[] = "{$cp->name} ansible_host={$cp->ipv4}";
        }
        $lines[] = '';

        $lines[] = '[workers]';
        foreach ($workers as $worker) {
            $lines[] = "{$worker->name} ansible_host={$worker->ipv4}";
        }
        $lines[] = '';

        $lines[] = '[cluster:children]';
        $lines[] = 'controlplane';
        $lines[] = 'workers';
        $lines[] = '';

        $lines[] = '[all:vars]';
        $lines[] = "ansible_user={$sshUser}";
        $lines[] = 'ansible_ssh_private_key_file=~/.ssh/node_key';
        $lines[] = "ansible_ssh_common_args='-o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null'";
        $lines[] = '';
        $lines[] = 'kubernetes_version=1.31';
        $lines[] = 'pod_cidr=10.244.0.0/16';
        $lines[] = 'service_cidr=10.96.0.0/12';
        $lines[] = '';

        $content = implode("\n", $lines);

        Storage::disk('local')->put("inventories/{$infrastructure->id}.ini", $content);
    }
}
