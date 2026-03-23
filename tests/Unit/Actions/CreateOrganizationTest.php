<?php

declare(strict_types=1);

use App\Actions\CreateOrganization;
use App\Data\CreateOrganizationData;

test('create organization', function (): void {

    $createOrganizationData = new CreateOrganizationData(
        name: 'Test Organization',
        description: 'This is a test organization',
    );

    $organization = new CreateOrganization()->handle($createOrganizationData);

    expect($organization->name)
        ->toBe('Test Organization');

});
