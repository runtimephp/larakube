<?php

declare(strict_types=1);

use App\Actions\CreateOrganization;
use App\Data\CreateOrganizationData;

test('creates organization',
    /**
     * @throws Throwable
     */
    function (): void {
        $action = app(CreateOrganization::class);

        $organization = $action->handle(new CreateOrganizationData(
            name: 'Test Organization',
            description: 'This is a test organization',
        ));

        expect($organization->name)
            ->toBe('Test Organization');
    });
