<?php

declare(strict_types=1);

use App\Actions\MarkClusterFailed;
use App\Enums\InfrastructureStatus;
use App\Models\Infrastructure;
use App\Models\KubernetesCluster;

test('marks cluster status as failed',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->create();

        /** @var KubernetesCluster $cluster */
        $cluster = KubernetesCluster::factory()->create([
            'infrastructure_id' => $infrastructure->id,
            'status' => InfrastructureStatus::Provisioning,
        ]);

        $action = new MarkClusterFailed;
        $action->handle($cluster);

        $cluster->refresh();

        expect($cluster->status)->toBe(InfrastructureStatus::Failed);
    });
