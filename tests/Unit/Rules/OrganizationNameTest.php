<?php

declare(strict_types=1);

use App\Rules\OrganizationName;
use Illuminate\Support\Facades\Validator;

test('rejects reserved names',
    /**
     * @throws Throwable
     */
    function (string $name): void {
        $validator = Validator::make(
            ['name' => $name],
            ['name' => [new OrganizationName]],
        );

        expect($validator->fails())->toBeTrue();
    })->with([
        'admin',
        'api',
        'auth',
        'dashboard',
        'settings',
        'billing',
        'login',
        'register',
    ]);

test('rejects reserved names case-insensitively',
    /**
     * @throws Throwable
     */
    function (): void {
        $validator = Validator::make(
            ['name' => 'Admin'],
            ['name' => [new OrganizationName]],
        );

        expect($validator->fails())->toBeTrue();
    });

test('rejects names matching route prefixes',
    /**
     * @throws Throwable
     */
    function (): void {
        $validator = Validator::make(
            ['name' => 'organizations'],
            ['name' => [new OrganizationName]],
        );

        expect($validator->fails())->toBeTrue();
    });

test('accepts valid organization names',
    /**
     * @throws Throwable
     */
    function (string $name): void {
        $validator = Validator::make(
            ['name' => $name],
            ['name' => [new OrganizationName]],
        );

        expect($validator->fails())->toBeFalse();
    })->with([
        'Acme Corp',
        'My Startup',
        'Tech Company 42',
        'João\'s Business',
    ]);
