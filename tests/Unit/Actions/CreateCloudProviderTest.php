<?php

declare(strict_types=1);

use App\Actions\CreateCloudProvider;
use App\Data\CreateCloudProviderData;
use App\Enums\CloudProviderType;
use App\Models\Organization;
use App\Services\InMemory\InMemoryHetznerFactory;

test('create cloud provider with valid token',
    /**
     * @throws Throwable
     */
    function (): void {
        $hetznerService = useInMemoryHetznerService(true);

        $organization = Organization::factory()->create();

        $action = new CreateCloudProvider(new InMemoryHetznerFactory($hetznerService));
        $cloudProvider = $action->handle(
            new CreateCloudProviderData(
                name: 'Hetzner Production',
                type: CloudProviderType::Hetzner,
                apiToken: 'valid-token',
            ),
            $organization,
        );

        expect($cloudProvider->name)->toBe('Hetzner Production')
            ->and($cloudProvider->type)->toBe(CloudProviderType::Hetzner)
            ->and($cloudProvider->is_verified)->toBeTrue()
            ->and($cloudProvider->organization_id)->toBe($organization->id);
    });

test('create cloud provider with invalid token throws exception',
    /**
     * @throws Throwable
     */
    function (): void {
        $hetznerService = useInMemoryHetznerService(false);

        $organization = Organization::factory()->create();

        $action = new CreateCloudProvider(new InMemoryHetznerFactory($hetznerService));
        $action->handle(
            new CreateCloudProviderData(
                name: 'Hetzner Staging',
                type: CloudProviderType::Hetzner,
                apiToken: 'invalid-token',
            ),
            $organization,
        );
    })->throws(RuntimeException::class, 'The API token for Hetzner is invalid.');
