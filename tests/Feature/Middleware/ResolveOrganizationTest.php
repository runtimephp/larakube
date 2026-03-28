<?php

declare(strict_types=1);

use App\Http\Middleware\ResolveOrganization;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\Route;

beforeEach(function (): void {
    Route::middleware(['auth:sanctum', ResolveOrganization::class])
        ->get('/test/org-middleware', fn () => response()->json(['ok' => true]));
});

test('resolves organization from header',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->create();
        $org = Organization::factory()->create();
        $user->organizations()->attach($org, ['role' => 'member']);

        $response = $this->actingAs($user, 'sanctum')
            ->withHeaders(['X-Organization-Id' => $org->id])
            ->getJson('/test/org-middleware');

        $response->assertOk();
    });

test('returns 422 when header is missing',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/test/org-middleware');

        $response->assertUnprocessable()
            ->assertJsonPath('code', 'validation_failed');
    });

test('returns 403 when user is not a member',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->create();
        $org = Organization::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->withHeaders(['X-Organization-Id' => $org->id])
            ->getJson('/test/org-middleware');

        $response->assertForbidden()
            ->assertJsonPath('code', 'forbidden');
    });

test('returns 404 when organization does not exist',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->withHeaders(['X-Organization-Id' => 'non-existent-id'])
            ->getJson('/test/org-middleware');

        $response->assertNotFound()
            ->assertJsonPath('code', 'not_found');
    });
