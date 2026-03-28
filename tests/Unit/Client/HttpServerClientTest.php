<?php

declare(strict_types=1);

use App\Client\HttpServerClient;
use App\Client\LarakubeClient;
use App\Data\CreateServerData;
use App\Data\ServerResourceData;
use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    $this->client = new HttpServerClient(
        new LarakubeClient(baseUrl: 'http://localhost:8000', token: '1|abc', organizationId: 'org-1', infrastructureId: 'infra-1'),
    );
});

test('create returns server resource data',
    /**
     * @throws Throwable
     */
    function (): void {
        Http::fake([
            '*/api/v1/servers' => Http::response([
                'data' => [
                    'id' => 'srv-1', 'name' => 'web-1', 'status' => 'running', 'type' => 'cx11',
                    'region' => 'fsn1', 'ipv4' => '1.2.3.4', 'ipv6' => null,
                    'external_id' => '123', 'cloud_provider_id' => 'cp-1', 'infrastructure_id' => 'infra-1',
                ],
            ], 201),
        ]);

        $result = $this->client->create(
            new CreateServerData(name: 'web-1', type: 'cx11', image: 'ubuntu-22.04', region: 'fsn1', infrastructure_id: 'infra-1'),
            'cp-1',
        );

        expect($result)->toBeInstanceOf(ServerResourceData::class)
            ->and($result->name)->toBe('web-1');
    });

test('list returns array of server resource data',
    /**
     * @throws Throwable
     */
    function (): void {
        Http::fake([
            '*/api/v1/servers' => Http::response([
                'data' => [
                    ['id' => 'srv-1', 'name' => 'web-1', 'status' => 'running', 'type' => 'cx11', 'region' => 'fsn1', 'ipv4' => null, 'ipv6' => null, 'external_id' => '123', 'cloud_provider_id' => 'cp-1', 'infrastructure_id' => 'infra-1'],
                ],
            ]),
        ]);

        $result = $this->client->list();

        expect($result)->toHaveCount(1)
            ->and($result[0]->name)->toBe('web-1');
    });

test('show returns server resource data',
    /**
     * @throws Throwable
     */
    function (): void {
        Http::fake([
            '*/api/v1/servers/*' => Http::response([
                'data' => [
                    'id' => 'srv-1', 'name' => 'web-1', 'status' => 'running', 'type' => 'cx11',
                    'region' => 'fsn1', 'ipv4' => '1.2.3.4', 'ipv6' => null,
                    'external_id' => '123', 'cloud_provider_id' => 'cp-1', 'infrastructure_id' => 'infra-1',
                ],
            ]),
        ]);

        $result = $this->client->show('srv-1');

        expect($result->name)->toBe('web-1');
    });

test('delete sends delete request',
    /**
     * @throws Throwable
     */
    function (): void {
        Http::fake([
            '*/api/v1/servers/*' => Http::response(null, 204),
        ]);

        $this->client->delete('srv-1');

        Http::assertSent(fn ($request): bool => $request->method() === 'DELETE'
            && str_contains((string) $request->url(), '/api/v1/servers/srv-1'));
    });

test('sync returns sync summary data',
    /**
     * @throws Throwable
     */
    function (): void {
        Http::fake([
            '*/api/v1/servers/sync' => Http::response([
                'data' => ['created' => 3, 'updated' => 1, 'deleted' => 2],
            ]),
        ]);

        $result = $this->client->sync('cp-1');

        expect($result)
            ->created->toBe(3)
            ->updated->toBe(1)
            ->deleted->toBe(2);
    });
