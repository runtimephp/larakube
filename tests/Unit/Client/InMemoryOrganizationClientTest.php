<?php

declare(strict_types=1);

use App\Client\InMemoryOrganizationClient;
use App\Data\CreateOrganizationData;
use App\Data\OrganizationData;
use App\Exceptions\LarakubeApiException;

beforeEach(function (): void {
    $this->client = new InMemoryOrganizationClient();
});

test('create returns configured organization data',
    /**
     * @throws Throwable
     */
    function (): void {
        $orgData = new OrganizationData(id: 'uuid-1', name: 'Acme', slug: 'acme');
        $this->client->setCreateResponse($orgData);

        $result = $this->client->create(new CreateOrganizationData(name: 'Acme'));

        expect($result)->toBe($orgData);
    });

test('create throws when configured to fail',
    /**
     * @throws Throwable
     */
    function (): void {
        $this->client->shouldFailCreate();

        $this->client->create(new CreateOrganizationData(name: 'Acme'));
    })->throws(LarakubeApiException::class);

test('list returns configured organizations',
    /**
     * @throws Throwable
     */
    function (): void {
        $orgs = [
            new OrganizationData(id: 'uuid-1', name: 'Acme', slug: 'acme'),
            new OrganizationData(id: 'uuid-2', name: 'Beta', slug: 'beta'),
        ];
        $this->client->setListResponse($orgs);

        $result = $this->client->list();

        expect($result)->toBe($orgs);
    });

test('list returns empty array by default',
    /**
     * @throws Throwable
     */
    function (): void {
        $result = $this->client->list();

        expect($result)->toBe([]);
    });
