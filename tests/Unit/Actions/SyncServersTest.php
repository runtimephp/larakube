<?php

declare(strict_types=1);

use App\Actions\SyncServers;
use App\Data\ServerData;
use App\Data\SyncSummaryData;
use App\Enums\ServerStatus;
use App\Models\CloudProvider;
use App\Models\Infrastructure;
use App\Models\Organization;
use App\Models\Server;

beforeEach(function (): void {
    $this->serverService = useInMemoryHetznerServerService();
    bindInMemoryHetznerFactory(serverService: $this->serverService);

    $this->organization = Organization::factory()->create();
    $this->provider = CloudProvider::factory()->hetzner()->create([
        'organization_id' => $this->organization->id,
    ]);
    $this->infrastructure = Infrastructure::factory()->create([
        'organization_id' => $this->organization->id,
        'cloud_provider_id' => $this->provider->id,
    ]);
});

test('creates new servers from remote',
    /**
     * @throws Throwable
     */
    function (): void {
        $this->serverService->addServer(new ServerData(
            externalId: '100',
            name: 'web-1',
            status: ServerStatus::Running,
            type: 'cx11',
            region: 'fsn1',
            ipv4: '1.2.3.4',
        ));

        $summary = app(SyncServers::class)->handle($this->provider, $this->infrastructure);

        expect($summary)
            ->toBeInstanceOf(SyncSummaryData::class)
            ->created->toBe(1)
            ->updated->toBe(0)
            ->deleted->toBe(0);

        $this->assertDatabaseHas('servers', [
            'external_id' => '100',
            'name' => 'web-1',
            'infrastructure_id' => $this->infrastructure->id,
        ]);
    });

test('updates existing servers from remote',
    /**
     * @throws Throwable
     */
    function (): void {
        Server::factory()->create([
            'organization_id' => $this->organization->id,
            'cloud_provider_id' => $this->provider->id,
            'infrastructure_id' => $this->infrastructure->id,
            'external_id' => '100',
            'name' => 'web-1',
            'status' => ServerStatus::Off,
            'ipv4' => '0.0.0.0',
        ]);

        $this->serverService->addServer(new ServerData(
            externalId: '100',
            name: 'web-1',
            status: ServerStatus::Running,
            type: 'cx11',
            region: 'fsn1',
            ipv4: '1.2.3.4',
        ));

        $summary = app(SyncServers::class)->handle($this->provider, $this->infrastructure);

        expect($summary)
            ->created->toBe(0)
            ->updated->toBe(1)
            ->deleted->toBe(0);

        $this->assertDatabaseHas('servers', [
            'external_id' => '100',
            'status' => 'running',
            'ipv4' => '1.2.3.4',
        ]);
    });

test('preserves infrastructure assignment on existing servers',
    /**
     * @throws Throwable
     */
    function (): void {
        $otherInfra = Infrastructure::factory()->create([
            'organization_id' => $this->organization->id,
            'cloud_provider_id' => $this->provider->id,
            'name' => 'Other Infra',
        ]);

        Server::factory()->create([
            'organization_id' => $this->organization->id,
            'cloud_provider_id' => $this->provider->id,
            'infrastructure_id' => $otherInfra->id,
            'external_id' => '100',
            'name' => 'web-1',
        ]);

        $this->serverService->addServer(new ServerData(
            externalId: '100',
            name: 'web-1',
            status: ServerStatus::Running,
            type: 'cx11',
            region: 'fsn1',
        ));

        app(SyncServers::class)->handle($this->provider, $this->infrastructure);

        $this->assertDatabaseHas('servers', [
            'external_id' => '100',
            'infrastructure_id' => $otherInfra->id,
        ]);
    });

test('deletes servers not present on remote',
    /**
     * @throws Throwable
     */
    function (): void {
        Server::factory()->create([
            'organization_id' => $this->organization->id,
            'cloud_provider_id' => $this->provider->id,
            'infrastructure_id' => $this->infrastructure->id,
            'external_id' => '999',
            'name' => 'deleted-server',
        ]);

        $summary = app(SyncServers::class)->handle($this->provider, $this->infrastructure);

        expect($summary)
            ->created->toBe(0)
            ->updated->toBe(0)
            ->deleted->toBe(1);

        $this->assertDatabaseMissing('servers', ['external_id' => '999']);
    });

test('returns correct counts for mixed operations',
    /**
     * @throws Throwable
     */
    function (): void {
        Server::factory()->create([
            'organization_id' => $this->organization->id,
            'cloud_provider_id' => $this->provider->id,
            'infrastructure_id' => $this->infrastructure->id,
            'external_id' => '100',
            'name' => 'existing',
            'status' => ServerStatus::Off,
        ]);

        Server::factory()->create([
            'organization_id' => $this->organization->id,
            'cloud_provider_id' => $this->provider->id,
            'infrastructure_id' => $this->infrastructure->id,
            'external_id' => '999',
            'name' => 'to-delete',
        ]);

        $this->serverService->addServer(new ServerData(
            externalId: '100', name: 'existing', status: ServerStatus::Running,
            type: 'cx11', region: 'fsn1',
        ));
        $this->serverService->addServer(new ServerData(
            externalId: '200', name: 'new-server', status: ServerStatus::Running,
            type: 'cx11', region: 'fsn1',
        ));

        $summary = app(SyncServers::class)->handle($this->provider, $this->infrastructure);

        expect($summary)
            ->created->toBe(1)
            ->updated->toBe(1)
            ->deleted->toBe(1);
    });
