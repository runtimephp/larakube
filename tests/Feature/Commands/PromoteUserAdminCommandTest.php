<?php

declare(strict_types=1);

use App\Enums\PlatformRole;
use App\Models\User;

test('promotes a user to admin',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->create([
            'email' => 'francisco@kuven.io',
        ]);

        $this->artisan('user:promote-admin', ['email' => 'francisco@kuven.io'])
            ->expectsOutputToContain('promoted to admin')
            ->assertSuccessful();

        $user->refresh();

        expect($user->platform_role)->toBe(PlatformRole::Admin);
    });

test('fails when user not found',
    /**
     * @throws Throwable
     */
    function (): void {
        $this->artisan('user:promote-admin', ['email' => 'nonexistent@kuven.io'])
            ->expectsOutputToContain('not found')
            ->assertFailed();
    });

test('reports when user is already admin',
    /**
     * @throws Throwable
     */
    function (): void {
        User::factory()->admin()->create([
            'email' => 'already@kuven.io',
        ]);

        $this->artisan('user:promote-admin', ['email' => 'already@kuven.io'])
            ->expectsOutputToContain('already an admin')
            ->assertSuccessful();
    });
