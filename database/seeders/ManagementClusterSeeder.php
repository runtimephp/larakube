<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ManagementClusterStatus;
use App\Models\ManagementCluster;
use Illuminate\Database\Seeder;

final class ManagementClusterSeeder extends Seeder
{
    public function run(): void
    {
        ManagementCluster::query()->firstOrCreate(
            ['provider' => 'hetzner', 'region' => 'eu-central'],
            ['name' => 'mgmt-production', 'status' => ManagementClusterStatus::Ready, 'kubernetes_version' => 'v1.32.3', 'kubeconfig' => 'apiVersion: v1\nclusters:\n- cluster:\n    server: https://127.0.0.1:6443'],
        );

        ManagementCluster::query()->firstOrCreate(
            ['provider' => 'hetzner', 'region' => 'us-east'],
            ['name' => 'mgmt-staging', 'status' => ManagementClusterStatus::Ready, 'kubernetes_version' => 'v1.31.4', 'kubeconfig' => 'apiVersion: v1\nclusters:\n- cluster:\n    server: https://127.0.0.1:6443'],
        );

        ManagementCluster::query()->firstOrCreate(
            ['provider' => 'docker', 'region' => 'local'],
            ['name' => 'mgmt-dev', 'status' => ManagementClusterStatus::Bootstrapping, 'kubernetes_version' => 'v1.32.3'],
        );
    }
}
