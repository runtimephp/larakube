<?php

declare(strict_types=1);

use App\Enums\ServerRole;
use App\Enums\ServerStatus;
use App\Models\CloudProvider;
use App\Models\Infrastructure;
use App\Models\Organization;
use App\Models\Server;
use App\Queries\ServerQuery;
use Illuminate\Database\Eloquent\Builder;

beforeEach(function (): void {
    $this->query = app(ServerQuery::class);
});

test('returns all servers',
    /**
     * @throws Throwable
     */
    function (): void {
        Server::factory()->count(3)->createQuietly();

        $servers = ($this->query)()->get();

        expect($servers)->toHaveCount(3);
    });

test('filters by organization',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Organization $organization */
        $organization = Organization::factory()->createQuietly();
        Server::factory()->for($organization)->count(2)->createQuietly();
        Server::factory()->createQuietly();

        $servers = ($this->query)()->byOrganization($organization)->get();

        expect($servers)->toHaveCount(2);
    });

test('filters by provider',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var CloudProvider $provider */
        $provider = CloudProvider::factory()->createQuietly();
        Server::factory()->for($provider, 'cloudProvider')->count(2)->createQuietly();
        Server::factory()->createQuietly();

        $servers = ($this->query)()->byProvider($provider)->get();

        expect($servers)->toHaveCount(2);
    });

test('filters by infrastructure',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->createQuietly();
        Server::factory()->for($infrastructure)->count(2)->createQuietly();
        Server::factory()->createQuietly();

        $servers = ($this->query)()->byInfrastructure($infrastructure)->get();

        expect($servers)->toHaveCount(2);
    });

test('filters by role',
    /**
     * @throws Throwable
     */
    function (): void {
        Server::factory()->createQuietly(['role' => ServerRole::ControlPlane]);
        Server::factory()->createQuietly(['role' => ServerRole::Node]);
        Server::factory()->createQuietly(['role' => ServerRole::Bastion]);

        $servers = ($this->query)()->byRole(ServerRole::ControlPlane)->get();

        expect($servers)->toHaveCount(1)
            ->and($servers->first()->role)->toBe(ServerRole::ControlPlane);
    });

test('filters by status',
    /**
     * @throws Throwable
     */
    function (): void {
        Server::factory()->running()->createQuietly();
        Server::factory()->off()->createQuietly();

        $servers = ($this->query)()->byStatus(ServerStatus::Off)->get();

        expect($servers)->toHaveCount(1)
            ->and($servers->first()->status)->toBe(ServerStatus::Off);
    });

test('filters by region',
    /**
     * @throws Throwable
     */
    function (): void {
        Server::factory()->createQuietly(['region' => 'fsn1']);
        Server::factory()->createQuietly(['region' => 'nbg1']);

        $servers = ($this->query)()->byRegion('fsn1')->get();

        expect($servers)->toHaveCount(1)
            ->and($servers->first()->region)->toBe('fsn1');
    });

test('filters by external id',
    /**
     * @throws Throwable
     */
    function (): void {
        Server::factory()->createQuietly(['external_id' => '12345']);
        Server::factory()->createQuietly(['external_id' => '67890']);

        /** @var Server $server */
        $server = ($this->query)()->byExternalId('12345')->first();

        expect($server)->not->toBeNull()
            ->and($server->external_id)->toBe('12345');
    });

test('filters running servers',
    /**
     * @throws Throwable
     */
    function (): void {
        Server::factory()->running()->createQuietly();
        Server::factory()->off()->createQuietly();
        Server::factory()->starting()->createQuietly();

        $servers = ($this->query)()->running()->get();

        expect($servers)->toHaveCount(1)
            ->and($servers->first()->status)->toBe(ServerStatus::Running);
    });

test('searches by name',
    /**
     * @throws Throwable
     */
    function (): void {
        Server::factory()->createQuietly(['name' => 'web-server-01']);
        Server::factory()->createQuietly(['name' => 'db-server-01']);

        $servers = ($this->query)()->search('web')->get();

        expect($servers)->toHaveCount(1)
            ->and($servers->first()->name)->toBe('web-server-01');
    });

test('orders by name',
    /**
     * @throws Throwable
     */
    function (): void {
        Server::factory()->createQuietly(['name' => 'charlie-server']);
        Server::factory()->createQuietly(['name' => 'alpha-server']);
        Server::factory()->createQuietly(['name' => 'beta-server']);

        $servers = ($this->query)()->ordered()->get();

        expect($servers->pluck('name')->toArray())->toBe(['alpha-server', 'beta-server', 'charlie-server']);
    });

test('chains multiple filters',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Organization $organization */
        $organization = Organization::factory()->createQuietly();
        /** @var CloudProvider $provider */
        $provider = CloudProvider::factory()->for($organization)->createQuietly();

        Server::factory()->for($organization)->for($provider, 'cloudProvider')->running()->createQuietly(['name' => 'web-01']);
        Server::factory()->for($organization)->for($provider, 'cloudProvider')->off()->createQuietly(['name' => 'web-02']);
        Server::factory()->running()->createQuietly(['name' => 'web-03']);

        $servers = ($this->query)()
            ->byOrganization($organization)
            ->byProvider($provider)
            ->running()
            ->get();

        expect($servers)->toHaveCount(1)
            ->and($servers->first()->name)->toBe('web-01');
    });

test('returns count',
    /**
     * @throws Throwable
     */
    function (): void {
        Server::factory()->count(5)->createQuietly();

        $count = ($this->query)()->count();

        expect($count)->toBe(5);
    });

test('paginates results',
    /**
     * @throws Throwable
     */
    function (): void {
        Server::factory()->count(20)->createQuietly();

        $paginated = ($this->query)()->paginate(10);

        expect($paginated->count())->toBe(10)
            ->and($paginated->total())->toBe(20);
    });

test('exposes the underlying builder',
    /**
     * @throws Throwable
     */
    function (): void {
        $builder = ($this->query)()->builder();

        expect($builder)->toBeInstanceOf(Builder::class);
    });

test('invoke returns independent clones',
    /**
     * @throws Throwable
     */
    function (): void {
        Server::factory()->createQuietly(['region' => 'fsn1']);
        Server::factory()->createQuietly(['region' => 'nbg1']);

        $query1 = ($this->query)()->byRegion('fsn1');
        $query2 = ($this->query)()->byRegion('nbg1');

        expect($query1->first()->region)->toBe('fsn1')
            ->and($query2->first()->region)->toBe('nbg1');
    });
