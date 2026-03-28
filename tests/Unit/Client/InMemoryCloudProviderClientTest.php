<?php

declare(strict_types=1);

use App\Client\InMemoryCloudProviderClient;
use App\Data\CloudProviderData;
use App\Data\CreateCloudProviderData;
use App\Enums\CloudProviderType;
use App\Exceptions\LarakubeApiException;

beforeEach(function (): void {
    $this->client = new InMemoryCloudProviderClient();
});

test('create returns configured data',
    /**
     * @throws Throwable
     */
    function (): void {
        $data = new CloudProviderData(id: 'uuid-1', name: 'Hetzner', type: 'hetzner', isVerified: true);
        $this->client->setCreateResponse($data);

        $result = $this->client->create(new CreateCloudProviderData(
            name: 'Hetzner',
            type: CloudProviderType::Hetzner,
            apiToken: 'token',
        ));

        expect($result)->toBe($data);
    });

test('create throws when configured to fail',
    /**
     * @throws Throwable
     */
    function (): void {
        $this->client->shouldFailCreate();

        $this->client->create(new CreateCloudProviderData(
            name: 'Hetzner',
            type: CloudProviderType::Hetzner,
            apiToken: 'token',
        ));
    })->throws(LarakubeApiException::class);

test('list returns configured data',
    /**
     * @throws Throwable
     */
    function (): void {
        $providers = [
            new CloudProviderData(id: 'uuid-1', name: 'Hetzner', type: 'hetzner', isVerified: true),
        ];
        $this->client->setListResponse($providers);

        expect($this->client->list())->toBe($providers);
    });

test('delete tracks the call',
    /**
     * @throws Throwable
     */
    function (): void {
        $this->client->delete('uuid-1');

        expect($this->client->deleteCalled)->toBeTrue()
            ->and($this->client->deletedId)->toBe('uuid-1');
    });

test('delete throws when configured to fail',
    /**
     * @throws Throwable
     */
    function (): void {
        $this->client->shouldFailDelete();

        $this->client->delete('uuid-1');
    })->throws(LarakubeApiException::class);
