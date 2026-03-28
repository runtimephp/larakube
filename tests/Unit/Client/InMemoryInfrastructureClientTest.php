<?php

declare(strict_types=1);

use App\Client\InMemoryInfrastructureClient;
use App\Data\CreateInfrastructureData;
use App\Data\InfrastructureData;
use App\Exceptions\LarakubeApiException;

beforeEach(function (): void {
    $this->client = new InMemoryInfrastructureClient();
});

test('create returns configured data',
    /**
     * @throws Throwable
     */
    function (): void {
        $data = new InfrastructureData(id: 'uuid-1', name: 'Prod', description: null, status: 'healthy', cloudProviderId: 'cp-1');
        $this->client->setCreateResponse($data);

        $result = $this->client->create(new CreateInfrastructureData(name: 'Prod'), 'cp-1');

        expect($result)->toBe($data);
    });

test('create throws when configured to fail',
    /**
     * @throws Throwable
     */
    function (): void {
        $this->client->shouldFailCreate();

        $this->client->create(new CreateInfrastructureData(name: 'Prod'), 'cp-1');
    })->throws(LarakubeApiException::class);

test('list returns configured data',
    /**
     * @throws Throwable
     */
    function (): void {
        $infras = [
            new InfrastructureData(id: 'uuid-1', name: 'Prod', description: null, status: 'healthy', cloudProviderId: 'cp-1'),
        ];
        $this->client->setListResponse($infras);

        expect($this->client->list())->toBe($infras);
    });

test('list returns empty by default',
    /**
     * @throws Throwable
     */
    function (): void {
        expect($this->client->list())->toBe([]);
    });

test('list throws when configured to fail',
    /**
     * @throws Throwable
     */
    function (): void {
        $this->client->shouldFailList();

        $this->client->list();
    })->throws(LarakubeApiException::class);
