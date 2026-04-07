<?php

declare(strict_types=1);

namespace App\Client;

use App\Contracts\ManagementClusterClient;
use App\Data\CreateManagementClusterData;
use App\Data\ManagementClusterData;

final readonly class HttpManagementClusterClient implements ManagementClusterClient
{
    public function __construct(
        private LarakubeClient $client,
    ) {}

    public function create(CreateManagementClusterData $data): ManagementClusterData
    {
        $response = $this->client->post('/api/v1/management-clusters', [
            'name' => $data->name,
            'provider' => $data->provider,
            'region' => $data->region,
            'kubernetes_version' => $data->kubernetesVersion,
        ]);

        return ManagementClusterData::fromArray($response->json('data'));
    }

    public function findByProviderAndRegion(string $provider, string $region): ?ManagementClusterData
    {
        $query = http_build_query(['provider' => $provider, 'region' => $region]);
        $response = $this->client->get("/api/v1/management-clusters?{$query}");

        $data = $response->json('data');

        if ($data === []) {
            return null;
        }

        return ManagementClusterData::fromArray($data[0]);
    }

    public function storeKubeconfig(string $id, string $kubeconfig): void
    {
        $this->client->patch("/api/v1/management-clusters/{$id}/kubeconfig", [
            'kubeconfig' => $kubeconfig,
        ]);
    }

    public function storeSshPrivateKey(string $id, string $sshPrivateKey): void
    {
        $this->client->patch("/api/v1/management-clusters/{$id}/ssh-key", [
            'ssh_private_key' => $sshPrivateKey,
        ]);
    }

    public function markReady(string $id): void
    {
        $this->client->patch("/api/v1/management-clusters/{$id}/ready");
    }

    public function delete(string $id): void
    {
        $this->client->delete("/api/v1/management-clusters/{$id}");
    }
}
