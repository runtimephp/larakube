<?php

declare(strict_types=1);

use App\Client\InMemoryManagementClusterClient;
use App\Data\CreateManagementClusterData;
use App\Exceptions\LarakubeApiException;

beforeEach(function (): void {
    $this->client = new InMemoryManagementClusterClient;
});

test('create returns management cluster data',
    /**
     * @throws Throwable
     */
    function (): void {
        $result = $this->client->create(new CreateManagementClusterData(
            name: 'kuven-mgmt-local',
            provider: 'docker',
            region: 'local',
        ));

        expect($result->name)->toBe('kuven-mgmt-local')
            ->and($result->provider)->toBe('docker')
            ->and($result->region)->toBe('local')
            ->and($result->status)->toBe('bootstrapping')
            ->and($result->id)->toBeUuid();
    });

test('find by provider and region returns matching cluster',
    /**
     * @throws Throwable
     */
    function (): void {
        $this->client->create(new CreateManagementClusterData(
            name: 'kuven-mgmt-local',
            provider: 'docker',
            region: 'local',
        ));

        $result = $this->client->findByProviderAndRegion('docker', 'local');

        expect($result)->not->toBeNull()
            ->and($result->provider)->toBe('docker')
            ->and($result->region)->toBe('local');
    });

test('find by provider and region returns null when not found',
    /**
     * @throws Throwable
     */
    function (): void {
        $result = $this->client->findByProviderAndRegion('docker', 'local');

        expect($result)->toBeNull();
    });

test('store kubeconfig persists kubeconfig',
    /**
     * @throws Throwable
     */
    function (): void {
        $cluster = $this->client->create(new CreateManagementClusterData(
            name: 'kuven-mgmt-local',
            provider: 'docker',
            region: 'local',
        ));

        $this->client->storeKubeconfig($cluster->id, 'apiVersion: v1');

        expect($this->client->getKubeconfig($cluster->id))->toBe('apiVersion: v1');
    });

test('mark ready updates cluster status',
    /**
     * @throws Throwable
     */
    function (): void {
        $cluster = $this->client->create(new CreateManagementClusterData(
            name: 'kuven-mgmt-local',
            provider: 'docker',
            region: 'local',
        ));

        $this->client->markReady($cluster->id);

        $updated = $this->client->findByProviderAndRegion('docker', 'local');

        expect($updated->status)->toBe('ready');
    });

test('delete removes cluster',
    /**
     * @throws Throwable
     */
    function (): void {
        $cluster = $this->client->create(new CreateManagementClusterData(
            name: 'kuven-mgmt-local',
            provider: 'docker',
            region: 'local',
        ));

        $this->client->delete($cluster->id);

        expect($this->client->findByProviderAndRegion('docker', 'local'))->toBeNull()
            ->and($this->client->getKubeconfig($cluster->id))->toBeNull();
    });

test('throws on store kubeconfig for unknown cluster',
    /**
     * @throws Throwable
     */
    function (): void {
        expect(fn () => $this->client->storeKubeconfig('nonexistent', 'kubeconfig'))
            ->toThrow(LarakubeApiException::class, 'Management cluster not found.');
    });

test('throws on mark ready for unknown cluster',
    /**
     * @throws Throwable
     */
    function (): void {
        expect(fn () => $this->client->markReady('nonexistent'))
            ->toThrow(LarakubeApiException::class, 'Management cluster not found.');
    });

test('throws on delete for unknown cluster',
    /**
     * @throws Throwable
     */
    function (): void {
        expect(fn () => $this->client->delete('nonexistent'))
            ->toThrow(LarakubeApiException::class, 'Management cluster not found.');
    });
