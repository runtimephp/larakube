<?php

declare(strict_types=1);

use App\Actions\CreateInfrastructure;
use App\Data\CreateInfrastructureData;
use App\Models\CloudProvider;

test('create infrastructure creates infrastructure for cloud provider',
    /**
     * @throws Throwable
     */
    function (): void {
        $provider = CloudProvider::factory()->hetzner()->create();

        $action = new CreateInfrastructure;
        $infrastructure = $action->handle(
            $provider,
            new CreateInfrastructureData(
                name: 'Production',
                description: 'Production infrastructure',
            ),
        );

        expect($infrastructure->name)->toBe('Production')
            ->and($infrastructure->description)->toBe('Production infrastructure')
            ->and($infrastructure->organization_id)->toBe($provider->organization_id)
            ->and($infrastructure->cloud_provider_id)->toBe($provider->id);

        $this->assertDatabaseHas('infrastructures', [
            'name' => 'Production',
            'description' => 'Production infrastructure',
            'cloud_provider_id' => $provider->id,
        ]);
    });

test('create infrastructure with null description',
    /**
     * @throws Throwable
     */
    function (): void {
        $provider = CloudProvider::factory()->hetzner()->create();

        $action = new CreateInfrastructure;
        $infrastructure = $action->handle(
            $provider,
            new CreateInfrastructureData(
                name: 'Staging',
            ),
        );

        expect($infrastructure->name)->toBe('Staging')
            ->and($infrastructure->description)->toBeNull();
    });
