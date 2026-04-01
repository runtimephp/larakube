<?php

declare(strict_types=1);

use App\Enums\OrganizationRole;
use App\Models\User;

test('create page can be rendered',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/organizations/create');

        $response->assertOk();
    });

test('store creates organization and redirects to dashboard',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/organizations', [
            'name' => 'Acme Corp',
            'description' => 'A test organization',
        ]);

        $response->assertRedirect('/acme-corp/dashboard');

        $user->refresh();
        expect($user->current_organization_id)->not->toBeNull()
            ->and($user->currentOrganization->name)->toBe('Acme Corp')
            ->and($user->currentOrganization->slug)->toBe('acme-corp')
            ->and($user->organizations->first()->pivot->role)->toBe(OrganizationRole::Owner);
    });

test('store validates required name',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/organizations', [
            'name' => '',
        ]);

        $response->assertSessionHasErrors('name');
    });

test('store rejects reserved organization names',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/organizations', [
            'name' => 'admin',
        ]);

        $response->assertSessionHasErrors('name');
    });

test('store handles duplicate slug with suffix',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->create();

        $this->actingAs($user)->post('/organizations', [
            'name' => 'Acme Corp',
        ]);

        $response = $this->actingAs($user)->post('/organizations', [
            'name' => 'Acme Corp',
        ]);

        $user->refresh();

        expect($user->organizations)->toHaveCount(2);

        $slugs = $user->organizations->pluck('slug')->toArray();
        expect($slugs)->toContain('acme-corp');
        expect(collect($slugs)->first(fn (string $s): bool => $s !== 'acme-corp'))->toStartWith('acme-corp-');
    });

test('redirects unauthenticated users to login',
    /**
     * @throws Throwable
     */
    function (): void {
        $this->get('/organizations/create')->assertRedirect('/login');
        $this->post('/organizations')->assertRedirect('/login');
    });
