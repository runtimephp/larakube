<?php

declare(strict_types=1);

use App\Client\HttpInfrastructureClient;
use App\Client\LarakubeClient;
use App\Data\CreateInfrastructureData;
use App\Data\InfrastructureData;
use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    $this->client = new HttpInfrastructureClient(
        new LarakubeClient(baseUrl: 'http://localhost:8000', token: '1|abc', organizationId: 'org-1'),
    );
});

test('create returns infrastructure data',
    /**
     * @throws Throwable
     */
    function (): void {
        Http::fake([
            '*/api/v1/infrastructures' => Http::response([
                'data' => [
                    'id' => 'uuid-1',
                    'name' => 'Production',
                    'description' => 'Prod infra',
                    'status' => 'healthy',
                    'cloud_provider_id' => 'cp-1',
                ],
            ], 201),
        ]);

        $result = $this->client->create(
            new CreateInfrastructureData(name: 'Production', description: 'Prod infra'),
            'cp-1',
        );

        expect($result)
            ->toBeInstanceOf(InfrastructureData::class)
            ->name->toBe('Production')
            ->cloudProviderId->toBe('cp-1');
    });

test('list returns array of infrastructure data',
    /**
     * @throws Throwable
     */
    function (): void {
        Http::fake([
            '*/api/v1/infrastructures' => Http::response([
                'data' => [
                    ['id' => 'uuid-1', 'name' => 'Prod', 'description' => null, 'status' => 'healthy', 'cloud_provider_id' => 'cp-1'],
                ],
            ]),
        ]);

        $result = $this->client->list();

        expect($result)->toHaveCount(1)
            ->and($result[0]->name)->toBe('Prod');
    });
