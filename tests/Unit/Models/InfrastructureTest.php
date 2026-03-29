<?php

declare(strict_types=1);

use App\Enums\InfrastructureStatus;
use App\Enums\ProvisioningPhase;
use App\Enums\ProvisioningStep;
use App\Models\Backup;
use App\Models\CloudProvider;
use App\Models\Firewall;
use App\Models\Infrastructure;
use App\Models\KubernetesCluster;
use App\Models\LoadBalancer;
use App\Models\Network;
use App\Models\Organization;
use App\Models\Region;
use App\Models\SshKey;
use App\Models\Storage;
use Carbon\CarbonImmutable;

test('casts provisioning step as enum',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->createQuietly([
            'provisioning_step' => ProvisioningStep::CreateBastion,
        ]);

        expect($infrastructure->provisioning_step)->toBe(ProvisioningStep::CreateBastion);
    });

test('casts provisioning phase as enum',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->createQuietly([
            'provisioning_phase' => ProvisioningPhase::Configuration,
        ]);

        expect($infrastructure->provisioning_phase)->toBe(ProvisioningPhase::Configuration);
    });

test('provisioning factory state sets step and phase',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->provisioning()->createQuietly();

        expect($infrastructure->status)->toBe(InfrastructureStatus::Provisioning)
            ->and($infrastructure->provisioning_step)->toBe(ProvisioningStep::GenerateSshKeypairs)
            ->and($infrastructure->provisioning_phase)->toBe(ProvisioningPhase::Infrastructure);
    });

test('creates infrastructure',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->create([
            'name' => 'prod-infra',
        ]);

        expect($infrastructure->name)->toBe('prod-infra')
            ->and($infrastructure->id)->toBeString()
            ->and($infrastructure->created_at)->toBeInstanceOf(CarbonImmutable::class);
    });

test('belongs to organization',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Organization $organization */
        $organization = Organization::factory()->create();

        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->create([
            'organization_id' => $organization->id,
        ]);

        expect($infrastructure->organization)->toBeInstanceOf(Organization::class)
            ->and($infrastructure->organization->id)->toBe($organization->id);
    });

test('belongs to cloud provider',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var CloudProvider $cloudProvider */
        $cloudProvider = CloudProvider::factory()->create();

        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->create([
            'cloud_provider_id' => $cloudProvider->id,
        ]);

        expect($infrastructure->cloudProvider)->toBeInstanceOf(CloudProvider::class)
            ->and($infrastructure->cloudProvider->id)->toBe($cloudProvider->id);
    });

test('belongs to region',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Region $region */
        $region = Region::factory()->create();

        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->create([
            'region_id' => $region->id,
        ]);

        expect($infrastructure->region)->toBeInstanceOf(Region::class)
            ->and($infrastructure->region->id)->toBe($region->id);
    });

test('has many kubernetes clusters',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->create();

        /** @var KubernetesCluster $cluster */
        $cluster = KubernetesCluster::factory()->create([
            'infrastructure_id' => $infrastructure->id,
        ]);

        expect($infrastructure->kubernetesClusters)->toHaveCount(1)
            ->and($infrastructure->kubernetesClusters->first())->toBeInstanceOf(KubernetesCluster::class)
            ->and($infrastructure->kubernetesClusters->first()->id)->toBe($cluster->id);
    });

test('has many networks',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->create();

        /** @var Network $network */
        $network = Network::factory()->create([
            'infrastructure_id' => $infrastructure->id,
        ]);

        expect($infrastructure->networks)->toHaveCount(1)
            ->and($infrastructure->networks->first())->toBeInstanceOf(Network::class)
            ->and($infrastructure->networks->first()->id)->toBe($network->id);
    });

test('has many firewalls',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->create();

        /** @var Firewall $firewall */
        $firewall = Firewall::factory()->create([
            'infrastructure_id' => $infrastructure->id,
        ]);

        expect($infrastructure->firewalls)->toHaveCount(1)
            ->and($infrastructure->firewalls->first())->toBeInstanceOf(Firewall::class)
            ->and($infrastructure->firewalls->first()->id)->toBe($firewall->id);
    });

test('has many load balancers',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->create();

        /** @var LoadBalancer $loadBalancer */
        $loadBalancer = LoadBalancer::factory()->create([
            'infrastructure_id' => $infrastructure->id,
        ]);

        expect($infrastructure->loadBalancers)->toHaveCount(1)
            ->and($infrastructure->loadBalancers->first())->toBeInstanceOf(LoadBalancer::class)
            ->and($infrastructure->loadBalancers->first()->id)->toBe($loadBalancer->id);
    });

test('has many storages',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->create();

        /** @var Storage $storage */
        $storage = Storage::factory()->create([
            'infrastructure_id' => $infrastructure->id,
        ]);

        expect($infrastructure->storages)->toHaveCount(1)
            ->and($infrastructure->storages->first())->toBeInstanceOf(Storage::class)
            ->and($infrastructure->storages->first()->id)->toBe($storage->id);
    });

test('has many backups',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->create();

        /** @var Backup $backup */
        $backup = Backup::factory()->create([
            'infrastructure_id' => $infrastructure->id,
        ]);

        expect($infrastructure->backups)->toHaveCount(1)
            ->and($infrastructure->backups->first())->toBeInstanceOf(Backup::class)
            ->and($infrastructure->backups->first()->id)->toBe($backup->id);
    });

test('has many ssh keys',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->create();

        /** @var SshKey $sshKey */
        $sshKey = SshKey::factory()->create([
            'infrastructure_id' => $infrastructure->id,
        ]);

        expect($infrastructure->sshKeys)->toHaveCount(1)
            ->and($infrastructure->sshKeys->first())->toBeInstanceOf(SshKey::class)
            ->and($infrastructure->sshKeys->first()->id)->toBe($sshKey->id);
    });

test('casts attributes correctly',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->create([
            'status' => InfrastructureStatus::Healthy,
        ]);

        expect($infrastructure->id)->toBeString()
            ->and($infrastructure->created_at)->toBeInstanceOf(CarbonImmutable::class)
            ->and($infrastructure->updated_at)->toBeInstanceOf(CarbonImmutable::class)
            ->and($infrastructure->status)->toBe(InfrastructureStatus::Healthy);
    });

test('uses uuid for primary key',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->create();

        expect($infrastructure->id)
            ->toBeString()
            ->toBeUuid();
    });

test('to array has all fields in correct order',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()
            ->create()
            ->refresh();

        expect(array_keys($infrastructure->toArray()))
            ->toBe([
                'id',
                'created_at',
                'updated_at',
                'organization_id',
                'cloud_provider_id',
                'region_id',
                'name',
                'description',
                'status',
                'provisioning_step',
                'provisioning_phase',
            ]);
    });
