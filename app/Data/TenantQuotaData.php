<?php

declare(strict_types=1);

namespace App\Data;

final readonly class TenantQuotaData
{
    public function __construct(
        public int $maxClusters = 10,
        public int $maxMachineDeployments = 50,
        public int $maxSecrets = 100,
    ) {}

    /**
     * @return array<string, string>
     */
    public function toKubernetesHard(): array
    {
        return [
            'count/clusters.cluster.x-k8s.io' => (string) $this->maxClusters,
            'count/machinedeployments.cluster.x-k8s.io' => (string) $this->maxMachineDeployments,
            'count/secrets' => (string) $this->maxSecrets,
        ];
    }
}
