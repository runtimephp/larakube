<?php

declare(strict_types=1);

use App\Actions\LoginUser;
use App\Console\Services\SessionManager;
use App\Data\SessionInfrastructureData;
use App\Data\SessionOrganizationData;
use App\Enums\InfrastructureStatus;
use App\Models\Firewall;
use App\Models\Infrastructure;
use App\Models\Network;
use App\Models\Organization;
use App\Models\Server;
use App\Models\SshKey;
use App\Models\User;
use App\Services\CloudProviderFactory;
use App\Services\InMemory\InMemoryCloudProviderFactory;
use App\Services\InMemory\InMemoryFirewallService;
use App\Services\InMemory\InMemoryHetznerServerService;
use App\Services\InMemory\InMemoryNetworkService;
use App\Services\InMemory\InMemorySshKeyService;

beforeEach(
    /**
     * @throws Throwable
     */
    function (): void {
        $this->app->singleton(SessionManager::class);
    });

test('fails when infrastructure is not found',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->create([
            'email' => 'missing@example.com',
            'password' => 'password123',
        ]);

        /** @var Organization $organization */
        $organization = Organization::factory()->create();
        $organization->users()->attach($user, ['role' => 'owner']);

        $userData = app(LoginUser::class)->handle('missing@example.com', 'password123');
        $session = app(SessionManager::class);
        $session->setUser($userData);
        $session->setOrganization(new SessionOrganizationData(
            id: $organization->id,
            name: $organization->name,
            slug: $organization->slug,
        ));
        $session->setInfrastructure(new SessionInfrastructureData(
            id: '00000000-0000-0000-0000-000000000099',
            name: 'nonexistent',
        ));

        $this->artisan('infrastructure:destroy')
            ->expectsOutputToContain('Infrastructure not found')
            ->assertFailed();
    });

test('outputs failures when destroy has cloud api errors',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->create([
            'email' => 'failures@example.com',
            'password' => 'password123',
        ]);

        /** @var Organization $organization */
        $organization = Organization::factory()->create();
        $organization->users()->attach($user, ['role' => 'owner']);

        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->createQuietly([
            'organization_id' => $organization->id,
            'status' => InfrastructureStatus::Failed,
        ]);

        Firewall::factory()->createQuietly([
            'infrastructure_id' => $infrastructure->id,
            'external_firewall_id' => 'fw-1',
        ]);

        $firewallService = new InMemoryFirewallService();
        $firewallService->shouldThrowOnDelete();

        $factory = new InMemoryCloudProviderFactory(
            serverService: new InMemoryHetznerServerService(),
            sshKeyService: new InMemorySshKeyService(),
            networkService: new InMemoryNetworkService(),
            firewallService: $firewallService,
        );
        $this->app->instance(CloudProviderFactory::class, $factory);

        $userData = app(LoginUser::class)->handle('failures@example.com', 'password123');
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

        $this->artisan('infrastructure:destroy')
            ->expectsConfirmation('This will destroy ALL resources for infrastructure "'.$infrastructure->name.'". Continue?', 'yes')
            ->expectsOutputToContain('cloud API failure')
            ->assertSuccessful();
    });

test('destroys all infrastructure resources and resets status',
    /**
     * @throws Throwable
     */
    function (): void {
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
            'status' => InfrastructureStatus::Failed,
        ]);

        Server::factory()->createQuietly(['infrastructure_id' => $infrastructure->id, 'external_id' => 'ext-1']);
        Server::factory()->createQuietly(['infrastructure_id' => $infrastructure->id, 'external_id' => 'ext-2']);
        SshKey::factory()->bastion()->createQuietly(['infrastructure_id' => $infrastructure->id, 'external_ssh_key_id' => 'key-1']);
        Network::factory()->createQuietly(['infrastructure_id' => $infrastructure->id, 'external_network_id' => 'net-1']);
        Firewall::factory()->createQuietly(['infrastructure_id' => $infrastructure->id, 'external_firewall_id' => 'fw-1']);

        $factory = Mockery::mock(CloudProviderFactory::class);
        $factory->shouldReceive('makeServerService')->andReturn(new InMemoryHetznerServerService());
        $factory->shouldReceive('makeSshKeyService')->andReturn(new InMemorySshKeyService());
        $factory->shouldReceive('makeNetworkService')->andReturn(new InMemoryNetworkService());
        $factory->shouldReceive('makeFirewallService')->andReturn(new InMemoryFirewallService());
        $this->app->instance(CloudProviderFactory::class, $factory);

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

        $this->artisan('infrastructure:destroy')
            ->expectsConfirmation('This will destroy ALL resources for infrastructure "'.$infrastructure->name.'". Continue?', 'yes')
            ->expectsOutputToContain('destroyed')
            ->assertSuccessful();

        expect(Server::where('infrastructure_id', $infrastructure->id)->count())->toBe(0)
            ->and(SshKey::where('infrastructure_id', $infrastructure->id)->count())->toBe(0)
            ->and(Network::where('infrastructure_id', $infrastructure->id)->count())->toBe(0)
            ->and(Firewall::where('infrastructure_id', $infrastructure->id)->count())->toBe(0);

        $infrastructure->refresh();

        expect($infrastructure->status)->toBe(InfrastructureStatus::Destroyed)
            ->and($infrastructure->provisioning_step)->toBeNull()
            ->and($infrastructure->provisioning_phase)->toBeNull();
    });

test('aborts when user declines confirmation',
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
        $infrastructure = Infrastructure::factory()->createQuietly([
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

        $this->artisan('infrastructure:destroy')
            ->expectsConfirmation('This will destroy ALL resources for infrastructure "'.$infrastructure->name.'". Continue?', 'no')
            ->expectsOutputToContain('Aborted')
            ->assertFailed();
    });

test('fails when not authenticated',
    /**
     * @throws Throwable
     */
    function (): void {
        $this->artisan('infrastructure:destroy')
            ->expectsOutputToContain('You are not logged in')
            ->assertFailed();
    });
