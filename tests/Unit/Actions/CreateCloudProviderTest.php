<?php

declare(strict_types=1);

use App\Actions\CreateCloudProvider;
use App\Data\CreateCloudProviderData;
use App\Enums\CloudProviderType;
use App\Models\Organization;

beforeEach(function (): void {
    $this->action = app(CreateCloudProvider::class);
});

test('creates cloud provider with valid token',
    /**
     * @throws Throwable
     */
    function (): void {
        $hetznerService = useInMemoryHetznerService(true);
        bindInMemoryHetznerFactory(validationService: $hetznerService);

        $this->action = app(CreateCloudProvider::class);

        /** @var Organization $organization */
        $organization = Organization::factory()->create();

        $cloudProvider = $this->action->handle(
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

test('throws exception with invalid token',
    /**
     * @throws Throwable
     */
    function (): void {
        $hetznerService = useInMemoryHetznerService(false);
        bindInMemoryHetznerFactory(validationService: $hetznerService);

        $this->action = app(CreateCloudProvider::class);

        /** @var Organization $organization */
        $organization = Organization::factory()->create();

        $this->action->handle(
            new CreateCloudProviderData(
                name: 'Hetzner Staging',
                type: CloudProviderType::Hetzner,
                apiToken: 'invalid-token',
            ),
            $organization,
        );
    })->throws(RuntimeException::class, 'The API token for Hetzner is invalid.');
