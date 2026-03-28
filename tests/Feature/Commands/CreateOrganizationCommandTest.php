<?php

declare(strict_types=1);

use App\Actions\LoginUser;
use App\Console\Services\SessionManager;
use App\Models\User;

beforeEach(function (): void {
    $this->app->singleton(SessionManager::class);
    $this->loginUser = app(LoginUser::class);
});

test('create organization command creates org and auto-selects it',
    /**
     * @throws Exception
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $userData = $this->loginUser->handle('john@example.com', 'password123');
        $session = app(SessionManager::class);
        $session->setUser($userData);

        $this->artisan('organization:create')
            ->expectsQuestion('Organization name', 'Acme Corp')
            ->expectsQuestion('Description', 'A great company')
            ->expectsOutputToContain('Organization [Acme Corp] created and selected')
            ->assertSuccessful();

        $this->assertDatabaseHas('organizations', ['name' => 'Acme Corp']);
        $this->assertDatabaseHas('organization_user', [
            'user_id' => $user->id,
            'role' => 'owner',
        ]);

        expect($session->hasOrganization())->toBeTrue()
            ->and($session->getOrganization()->name)->toBe('Acme Corp');
    });

test('create organization command fails when not authenticated',
    /**
     * @throws Throwable
     */
    function (): void {
        $this->artisan('organization:create')
            ->expectsOutputToContain('You are not logged in')
            ->assertFailed();
    });
