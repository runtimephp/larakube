<?php

declare(strict_types=1);

use App\Actions\CreateOrganization;
use App\Data\CreateOrganizationData;
use App\Models\Organization;

test('generates slug from name on creation',
    /**
     * @throws Throwable
     */
    function (): void {
        $action = app(CreateOrganization::class);

        $organization = $action->handle(new CreateOrganizationData(
            name: 'Acme Corp',
        ));

        expect($organization->slug)->toBe('acme-corp');
    });

test('appends random suffix when slug already exists',
    /**
     * @throws Throwable
     */
    function (): void {
        $action = app(CreateOrganization::class);

        /** @var Organization $first */
        $first = $action->handle(new CreateOrganizationData(
            name: 'Acme Corp',
        ));

        /** @var Organization $second */
        $second = $action->handle(new CreateOrganizationData(
            name: 'Acme Corp',
        ));

        expect($first->slug)->toBe('acme-corp')
            ->and($second->slug)->toStartWith('acme-corp-')
            ->and($second->slug)->not->toBe('acme-corp')
            ->and(mb_strlen($second->slug))->toBe(mb_strlen('acme-corp-') + 4);
    });

test('does not overwrite slug if already set',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Organization $organization */
        $organization = Organization::factory()->create([
            'name' => 'Acme Corp',
            'slug' => 'custom-slug',
        ]);

        expect($organization->slug)->toBe('custom-slug');
    });
