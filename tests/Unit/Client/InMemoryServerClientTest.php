<?php

declare(strict_types=1);

use App\Client\InMemoryServerClient;
use App\Data\CreateServerData;
use App\Data\ServerResourceData;
use App\Exceptions\LarakubeApiException;

beforeEach(function (): void {
    $this->client = new InMemoryServerClient();
});

test('create returns configured data',
    /**
     * @throws Throwable
     */
    function (): void {
        $data = new ServerResourceData(id: 'srv-1', name: 'web-1', status: 'running', type: 'cx11', region: 'fsn1', ipv4: null, ipv6: null, externalId: '123', cloudProviderId: 'cp-1', infrastructureId: 'infra-1');
        $this->client->setCreateResponse($data);

        $result = $this->client->create(new CreateServerData(name: 'web-1', type: 'cx11', image: 'ubuntu-22.04', region: 'fsn1', infrastructure_id: 'infra-1'), 'cp-1');

        expect($result)->toBe($data);
    });

test('create throws when configured to fail',
    /**
     * @throws Throwable
     */
    function (): void {
        $this->client->shouldFailCreate();

        $this->client->create(new CreateServerData(name: 'web-1', type: 'cx11', image: 'ubuntu-22.04', region: 'fsn1', infrastructure_id: 'infra-1'), 'cp-1');
    })->throws(LarakubeApiException::class);

test('list returns configured data',
    /**
     * @throws Throwable
     */
    function (): void {
        $servers = [
            new ServerResourceData(id: 'srv-1', name: 'web-1', status: 'running', type: 'cx11', region: 'fsn1', ipv4: null, ipv6: null, externalId: '123', cloudProviderId: 'cp-1', infrastructureId: 'infra-1'),
        ];
        $this->client->setListResponse($servers);

        expect($this->client->list())->toBe($servers);
    });

test('list throws when configured to fail',
    /**
     * @throws Throwable
     */
    function (): void {
        $this->client->shouldFailList();

        $this->client->list();
    })->throws(LarakubeApiException::class);

test('show returns configured data',
    /**
     * @throws Throwable
     */
    function (): void {
        $data = new ServerResourceData(id: 'srv-1', name: 'web-1', status: 'running', type: 'cx11', region: 'fsn1', ipv4: null, ipv6: null, externalId: '123', cloudProviderId: 'cp-1', infrastructureId: 'infra-1');
        $this->client->setShowResponse($data);

        expect($this->client->show('srv-1'))->toBe($data);
    });

test('show throws when configured to fail',
    /**
     * @throws Throwable
     */
    function (): void {
        $this->client->shouldFailShow();

        $this->client->show('srv-1');
    })->throws(LarakubeApiException::class);

test('delete tracks the call',
    /**
     * @throws Throwable
     */
    function (): void {
        $this->client->delete('srv-1');

        expect($this->client->deleteCalled)->toBeTrue()
            ->and($this->client->deletedId)->toBe('srv-1');
    });

test('delete throws when configured to fail',
    /**
     * @throws Throwable
     */
    function (): void {
        $this->client->shouldFailDelete();

        $this->client->delete('srv-1');
    })->throws(LarakubeApiException::class);
