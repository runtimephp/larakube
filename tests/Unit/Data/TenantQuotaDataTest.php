<?php

declare(strict_types=1);

use App\Data\TenantQuotaData;

test('converts to kubernetes hard limits with defaults',
    /**
     * @throws Throwable
     */
    function (): void {
        $quota = new TenantQuotaData;

        expect($quota->toKubernetesHard())->toBe([
            'count/clusters.cluster.x-k8s.io' => '10',
            'count/machinedeployments.cluster.x-k8s.io' => '50',
            'count/secrets' => '100',
        ]);
    });

test('converts to kubernetes hard limits with custom values',
    /**
     * @throws Throwable
     */
    function (): void {
        $quota = new TenantQuotaData(
            maxClusters: 5,
            maxMachineDeployments: 20,
            maxSecrets: 50,
        );

        expect($quota->toKubernetesHard())->toBe([
            'count/clusters.cluster.x-k8s.io' => '5',
            'count/machinedeployments.cluster.x-k8s.io' => '20',
            'count/secrets' => '50',
        ]);
    });
