<?php

declare(strict_types=1);

use App\Actions\GenerateInventory;
use App\Enums\ServerRole;
use App\Enums\ServerStatus;
use App\Models\Infrastructure;
use App\Models\Server;
use App\Queries\ServerQuery;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

test('generates inventory ini and stores to disk',
    /**
     * @throws Throwable
     */
    function (): void {
        Storage::fake('local');

        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->provisioning()->createQuietly();

        Server::factory()->createQuietly([
            'infrastructure_id' => $infrastructure->id,
            'role' => ServerRole::Bastion,
            'status' => ServerStatus::Running,
            'name' => 'prod-bastion',
            'ipv4' => '10.0.0.1',
        ]);

        Server::factory()->createQuietly([
            'infrastructure_id' => $infrastructure->id,
            'role' => ServerRole::ControlPlane,
            'status' => ServerStatus::Running,
            'name' => 'prod-cp-1',
            'ipv4' => '10.0.1.1',
        ]);

        Server::factory()->createQuietly([
            'infrastructure_id' => $infrastructure->id,
            'role' => ServerRole::Node,
            'status' => ServerStatus::Running,
            'name' => 'prod-worker-1',
            'ipv4' => '10.0.2.1',
        ]);

        $action = new GenerateInventory(new ServerQuery());
        $action->handle($infrastructure);

        Storage::disk('local')->assertExists("inventories/{$infrastructure->id}.ini");

        $inventory = Storage::disk('local')->get("inventories/{$infrastructure->id}.ini");

        expect($inventory)->toContain('[bastion]')
            ->and($inventory)->toContain('prod-bastion ansible_host=10.0.0.1')
            ->and($inventory)->toContain('[controlplane]')
            ->and($inventory)->toContain('prod-cp-1 ansible_host=10.0.1.1')
            ->and($inventory)->toContain('[workers]')
            ->and($inventory)->toContain('prod-worker-1 ansible_host=10.0.2.1')
            ->and($inventory)->toContain('[cluster:children]')
            ->and($inventory)->toContain('pod_cidr=10.244.0.0/16');
    });

test('inventoryPath returns correct path',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->provisioning()->createQuietly();

        $path = GenerateInventory::inventoryPath($infrastructure);

        expect($path)->toContain("inventories/{$infrastructure->id}.ini");
    });

test('includes gateway ip when cached',
    /**
     * @throws Throwable
     */
    function (): void {
        Storage::fake('local');

        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->provisioning()->createQuietly();

        Server::factory()->createQuietly([
            'infrastructure_id' => $infrastructure->id,
            'role' => ServerRole::Bastion,
            'status' => ServerStatus::Running,
            'name' => 'prod-bastion',
            'ipv4' => '10.0.0.1',
        ]);

        Server::factory()->createQuietly([
            'infrastructure_id' => $infrastructure->id,
            'role' => ServerRole::ControlPlane,
            'status' => ServerStatus::Running,
            'name' => 'prod-cp-1',
            'ipv4' => '10.0.1.1',
        ]);

        Cache::put("infrastructure.{$infrastructure->id}.gateway_ip", '10.0.0.254', now()->addHour());

        $action = new GenerateInventory(new ServerQuery());
        $action->handle($infrastructure);

        $inventory = Storage::disk('local')->get("inventories/{$infrastructure->id}.ini");

        expect($inventory)->toContain('nat_gateway_ip=10.0.0.254');
    });

test('includes control plane endpoint when multiple control planes',
    /**
     * @throws Throwable
     */
    function (): void {
        Storage::fake('local');

        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->provisioning()->createQuietly();

        Server::factory()->createQuietly([
            'infrastructure_id' => $infrastructure->id,
            'role' => ServerRole::Bastion,
            'status' => ServerStatus::Running,
            'name' => 'prod-bastion',
            'ipv4' => '10.0.0.1',
        ]);

        Server::factory()->createQuietly([
            'infrastructure_id' => $infrastructure->id,
            'role' => ServerRole::ControlPlane,
            'status' => ServerStatus::Running,
            'name' => 'prod-cp-1',
            'ipv4' => '10.0.1.1',
        ]);

        Server::factory()->createQuietly([
            'infrastructure_id' => $infrastructure->id,
            'role' => ServerRole::ControlPlane,
            'status' => ServerStatus::Running,
            'name' => 'prod-cp-2',
            'ipv4' => '10.0.1.2',
        ]);

        $action = new GenerateInventory(new ServerQuery());
        $action->handle($infrastructure);

        $inventory = Storage::disk('local')->get("inventories/{$infrastructure->id}.ini");

        expect($inventory)->toContain('control_plane_endpoint=');
    });
