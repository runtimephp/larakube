<?php

declare(strict_types=1);

use App\Models\Organization;

test('to array', function (): void {

    /** @var Organization $organization */
    $organization = Organization::factory()
        ->create()
        ->fresh();

    expect(array_keys($organization->toArray()))
        ->toBe([
            'id',
            'created_at',
            'updated_at',
            'name',
            'slug',
            'logo',
            'description',
        ]);


});
