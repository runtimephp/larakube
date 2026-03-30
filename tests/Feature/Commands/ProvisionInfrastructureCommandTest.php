<?php

declare(strict_types=1);

use App\Actions\LoginUser;
use App\Console\Services\SessionManager;
use App\Data\SessionInfrastructureData;
use App\Data\SessionOrganizationData;
use App\Enums\InfrastructureStatus;
use App\Enums\ProvisioningPhase;
use App\Enums\ProvisioningStep;
use App\Jobs\ProcessProvisioningStep;
use App\Models\Infrastructure;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\Bus;

beforeEach(
    /**
     * @throws Throwable
     */
    function (): void {
        $this->app->singleton(SessionManager::class);
    });

test('dispatches provisioning job for infrastructure',
    /**
     * @throws Throwable
     */
    function (): void {
        Bus::fake([ProcessProvisioningStep::class]);

        /** @var User $user */
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        /** @var Organization $organization */
        $organization = Organization::factory()->create();
        $organization->users()->attach($user, ['role' => 'owner']);

        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->createQuietly([
            'organization_id' => $organization->id,
            'status' => InfrastructureStatus::Healthy,
        ]);

        $userData = app(LoginUser::class)->handle('john@example.com', 'password123');
        $session = app(SessionManager::class);
        $session->setUser($userData);
        $session->setOrganization(new SessionOrganizationData(
            id: $organization->id,
            name: $organization->name,
            slug: $organization->slug,
        ));
        $session->setInfrastructure(new SessionInfrastructureData(
            id: $infrastructure->id,
            name: $infrastructure->name,
        ));

        $this->artisan('infrastructure:provision')
            ->expectsOutputToContain('Provisioning started')
            ->assertSuccessful();

        $infrastructure->refresh();

        expect($infrastructure->status)->toBe(InfrastructureStatus::Provisioning)
            ->and($infrastructure->provisioning_step)->toBe(ProvisioningStep::GenerateSshKeypairs)
            ->and($infrastructure->provisioning_phase)->toBe(ProvisioningPhase::Infrastructure);

        Bus::assertDispatched(ProcessProvisioningStep::class);
    });

test('rejects already provisioning infrastructure',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->create([
            'email' => 'jane@example.com',
            'password' => 'password123',
        ]);

        /** @var Organization $organization */
        $organization = Organization::factory()->create();
        $organization->users()->attach($user, ['role' => 'owner']);

        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->provisioning()->createQuietly([
            'organization_id' => $organization->id,
        ]);

        $userData = app(LoginUser::class)->handle('jane@example.com', 'password123');
        $session = app(SessionManager::class);
        $session->setUser($userData);
        $session->setOrganization(new SessionOrganizationData(
            id: $organization->id,
            name: $organization->name,
            slug: $organization->slug,
        ));
        $session->setInfrastructure(new SessionInfrastructureData(
            id: $infrastructure->id,
            name: $infrastructure->name,
        ));

        $this->artisan('infrastructure:provision')
            ->expectsOutputToContain('already being provisioned')
            ->assertFailed();
    });

test('fails when not authenticated',
    /**
     * @throws Throwable
     */
    function (): void {
        $this->artisan('infrastructure:provision')
            ->expectsOutputToContain('You are not logged in')
            ->assertFailed();
    });
