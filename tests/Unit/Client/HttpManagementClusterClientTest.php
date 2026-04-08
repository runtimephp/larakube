<?php

declare(strict_types=1);

use App\Client\HttpManagementClusterClient;
use App\Client\LarakubeClient;
use App\Data\CreateManagementClusterData;
use App\Data\ManagementClusterData;
use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    $this->larakubeClient = new LarakubeClient(
        baseUrl: 'http://localhost:8000',
        token: '1|abc123',
    );

    $this->client = new HttpManagementClusterClient($this->larakubeClient);
});

test('create returns management cluster data',
    /**
     * @throws Throwable
     */
    function (): void {
        Http::fake([
            '*/api/v1/management-clusters' => Http::response([
                'data' => [
                    'id' => 'uuid-123',
                    'name' => 'kuven-mgmt-local',
                    'provider_id' => 'docker',
                    'platform_region_id' => 'local',
                    'status' => 'bootstrapping',
                    'version' => 'v1.32.3',
                ],
            ], 201),
        ]);

        $result = $this->client->create(new CreateManagementClusterData(
            name: 'kuven-mgmt-local',
            providerId: 'docker',
            platformRegionId: 'local',
            version: 'v1.32.3',
        ));

        expect($result)
            ->toBeInstanceOf(ManagementClusterData::class)
            ->and($result->id)->toBe('uuid-123')
            ->and($result->name)->toBe('kuven-mgmt-local');
    });

test('find by provider and region returns first matching cluster',
    /**
     * @throws Throwable
     */
    function (): void {
        Http::fake([
            '*/api/v1/management-clusters?*' => Http::response([
                'data' => [
                    [
                        'id' => 'uuid-123',
                        'name' => 'kuven-mgmt-local',
                        'provider_id' => 'docker',
                        'platform_region_id' => 'local',
                        'status' => 'ready',
                        'version' => 'v1.32.3',
                    ],
                ],
            ]),
        ]);

        $result = $this->client->findByProviderAndRegion('docker', 'local');

        expect($result)
            ->toBeInstanceOf(ManagementClusterData::class)
            ->and($result->name)->toBe('kuven-mgmt-local');
    });

test('find by provider and region returns null when empty',
    /**
     * @throws Throwable
     */
    function (): void {
        Http::fake([
            '*/api/v1/management-clusters?*' => Http::response([
                'data' => [],
            ]),
        ]);

        $result = $this->client->findByProviderAndRegion('docker', 'nonexistent');

        expect($result)->toBeNull();
    });

test('store kubeconfig sends patch request',
    /**
     * @throws Throwable
     */
    function (): void {
        Http::fake([
            '*/api/v1/management-clusters/uuid-123/kubeconfig' => Http::response(null, 204),
        ]);

        $this->client->storeKubeconfig('uuid-123', 'apiVersion: v1');

        Http::assertSent(fn ($request): bool => $request->url() === 'http://localhost:8000/api/v1/management-clusters/uuid-123/kubeconfig'
            && $request['kubeconfig'] === 'apiVersion: v1');
    });

test('mark ready sends patch request',
    /**
     * @throws Throwable
     */
    function (): void {
        Http::fake([
            '*/api/v1/management-clusters/uuid-123/ready' => Http::response(null, 204),
        ]);

        $this->client->markReady('uuid-123');

        Http::assertSent(fn ($request): bool => $request->url() === 'http://localhost:8000/api/v1/management-clusters/uuid-123/ready');
    });

test('delete sends delete request',
    /**
     * @throws Throwable
     */
    function (): void {
        Http::fake([
            '*/api/v1/management-clusters/uuid-123' => Http::response(null, 204),
        ]);

        $this->client->delete('uuid-123');

        Http::assertSent(fn ($request): bool => $request->method() === 'DELETE'
            && $request->url() === 'http://localhost:8000/api/v1/management-clusters/uuid-123');
    });
