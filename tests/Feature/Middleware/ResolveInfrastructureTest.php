<?php

declare(strict_types=1);

use App\Http\Middleware\ResolveInfrastructure;
use App\Http\Middleware\ResolveOrganization;
use App\Models\Infrastructure;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\Route;

beforeEach(function (): void {
    Route::middleware(['auth:sanctum', ResolveOrganization::class, ResolveInfrastructure::class])
        ->get('/test/infra-middleware', fn () => response()->json(['ok' => true]));
});

test('resolves infrastructure from header',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->create();
        $org = Organization::factory()->create();
        $user->organizations()->attach($org, ['role' => 'member']);
        $infra = Infrastructure::factory()->create(['organization_id' => $org->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->withHeaders([
                'X-Organization-Id' => $org->id,
                'X-Infrastructure-Id' => $infra->id,
            ])
            ->getJson('/test/infra-middleware');

        $response->assertOk();
    });

test('returns 422 when header is missing',
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
            ->getJson('/test/infra-middleware');

        $response->assertUnprocessable()
            ->assertJsonPath('code', 'validation_failed');
    });

test('returns 404 when infrastructure does not exist',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->create();
        $org = Organization::factory()->create();
        $user->organizations()->attach($org, ['role' => 'member']);

        $response = $this->actingAs($user, 'sanctum')
            ->withHeaders([
                'X-Organization-Id' => $org->id,
                'X-Infrastructure-Id' => 'non-existent',
            ])
            ->getJson('/test/infra-middleware');

        $response->assertNotFound()
            ->assertJsonPath('code', 'not_found');
    });

test('returns 403 when infrastructure belongs to another organization',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->create();
        $org = Organization::factory()->create();
        $user->organizations()->attach($org, ['role' => 'member']);
        $otherOrg = Organization::factory()->create();
        $infra = Infrastructure::factory()->create(['organization_id' => $otherOrg->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->withHeaders([
                'X-Organization-Id' => $org->id,
                'X-Infrastructure-Id' => $infra->id,
            ])
            ->getJson('/test/infra-middleware');

        $response->assertForbidden()
            ->assertJsonPath('code', 'forbidden');
    });
