<?php

declare(strict_types=1);

use App\Enums\OrganizationRole;

test('label returns correct labels', function (): void {
    expect(OrganizationRole::Owner->label())->toBe('Owner')
        ->and(OrganizationRole::Admin->label())->toBe('Admin')
        ->and(OrganizationRole::Member->label())->toBe('Member');
});

test('cases returns all cases', function (): void {
    expect(OrganizationRole::cases())->toBe([
        OrganizationRole::Owner,
        OrganizationRole::Admin,
        OrganizationRole::Member,
    ]);
});

test('values are correct strings', function (): void {
    expect(OrganizationRole::Owner->value)->toBe('owner')
        ->and(OrganizationRole::Admin->value)->toBe('admin')
        ->and(OrganizationRole::Member->value)->toBe('member');
});
