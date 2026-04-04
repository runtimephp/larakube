<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Data\CreateManagementClusterData;
use App\Data\ManagementClusterData;
use App\Exceptions\LarakubeApiException;

interface ManagementClusterClient
{
    /**
     * @throws LarakubeApiException
     */
    public function create(CreateManagementClusterData $data): ManagementClusterData;

    /**
     * @throws LarakubeApiException
     */
    public function findByProviderAndRegion(string $provider, string $region): ?ManagementClusterData;

    /**
     * @throws LarakubeApiException
     */
    public function storeKubeconfig(string $id, string $kubeconfig): void;

    /**
     * @throws LarakubeApiException
     */
    public function markReady(string $id): void;

    /**
     * @throws LarakubeApiException
     */
    public function delete(string $id): void;
}
