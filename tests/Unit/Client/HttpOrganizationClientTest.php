<?php

declare(strict_types=1);

use App\Client\HttpOrganizationClient;
use App\Client\LarakubeClient;
use App\Data\CreateOrganizationData;
use App\Data\OrganizationData;
use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    $this->larakubeClient = new LarakubeClient(
        baseUrl: 'http://localhost:8000',
        token: '1|abc123',
    );

    $this->client = new HttpOrganizationClient($this->larakubeClient);
});

test('create returns organization data',
    /**
     * @throws Throwable
     */
    function (): void {
        Http::fake([
            '*/api/v1/organizations' => Http::response([
                'data' => [
                    'id' => 'uuid-123',
                    'name' => 'Acme Corp',
                    'slug' => 'acme-corp',
                    'description' => 'A great company',
                ],
            ], 201),
        ]);

        $result = $this->client->create(new CreateOrganizationData(
            name: 'Acme Corp',
            description: 'A great company',
        ));

        expect($result)
            ->toBeInstanceOf(OrganizationData::class)
            ->id->toBe('uuid-123')
            ->name->toBe('Acme Corp')
            ->slug->toBe('acme-corp');
    });

test('list returns array of organization data',
    /**
     * @throws Throwable
     */
    function (): void {
        Http::fake([
            '*/api/v1/organizations' => Http::response([
                'data' => [
                    ['id' => 'uuid-1', 'name' => 'Acme', 'slug' => 'acme', 'description' => null],
                    ['id' => 'uuid-2', 'name' => 'Beta', 'slug' => 'beta', 'description' => null],
                ],
            ]),
        ]);

        $result = $this->client->list();

        expect($result)->toHaveCount(2)
            ->and($result[0])->toBeInstanceOf(OrganizationData::class)
            ->and($result[0]->name)->toBe('Acme')
            ->and($result[1]->name)->toBe('Beta');
    });
