<?php

declare(strict_types=1);

use App\Client\HttpCloudProviderClient;
use App\Client\LarakubeClient;
use App\Data\CloudProviderData;
use App\Data\CreateCloudProviderData;
use App\Enums\CloudProviderType;
use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    $this->client = new HttpCloudProviderClient(
        new LarakubeClient(baseUrl: 'http://localhost:8000', token: '1|abc', organizationId: 'org-1'),
    );
});

test('create returns cloud provider data',
    /**
     * @throws Throwable
     */
    function (): void {
        Http::fake([
            '*/api/v1/cloud-providers' => Http::response([
                'data' => ['id' => 'uuid-1', 'name' => 'Hetzner', 'type' => 'hetzner', 'is_verified' => true],
            ], 201),
        ]);

        $result = $this->client->create(new CreateCloudProviderData(
            name: 'Hetzner',
            type: CloudProviderType::Hetzner,
            apiToken: 'token',
        ));

        expect($result)
            ->toBeInstanceOf(CloudProviderData::class)
            ->name->toBe('Hetzner');
    });

test('list returns array of cloud provider data',
    /**
     * @throws Throwable
     */
    function (): void {
        Http::fake([
            '*/api/v1/cloud-providers' => Http::response([
                'data' => [
                    ['id' => 'uuid-1', 'name' => 'Hetzner', 'type' => 'hetzner', 'is_verified' => true],
                ],
            ]),
        ]);

        $result = $this->client->list();

        expect($result)->toHaveCount(1)
            ->and($result[0]->name)->toBe('Hetzner');
    });

test('delete sends delete request',
    /**
     * @throws Throwable
     */
    function (): void {
        Http::fake([
            '*/api/v1/cloud-providers/*' => Http::response(null, 204),
        ]);

        $this->client->delete('uuid-1');

        Http::assertSent(fn ($request): bool => $request->method() === 'DELETE'
            && str_contains((string) $request->url(), '/api/v1/cloud-providers/uuid-1'));
    });
