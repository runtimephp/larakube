<?php

declare(strict_types=1);

use App\Enums\InfrastructureStatus;
use App\Models\CloudProvider;
use App\Models\Infrastructure;
use App\Models\Organization;
use App\Queries\InfrastructureQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;

beforeEach(function (): void {
    $this->query = app(InfrastructureQuery::class);
});

test('first or fail throws when not found',
    /**
     * @throws Throwable
     */
    function (): void {
        ($this->query)()->byId('nonexistent-id')->firstOrFail();
    })->throws(ModelNotFoundException::class);

test('first or fail returns infrastructure when found',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->createQuietly();

        $result = ($this->query)()->byId($infrastructure->id)->firstOrFail();

        expect($result->id)->toBe($infrastructure->id);
    });

test('returns all infrastructures',
    /**
     * @throws Throwable
     */
    function (): void {
        Infrastructure::factory()->count(3)->createQuietly();

        $infrastructures = ($this->query)()->get();

        expect($infrastructures)->toHaveCount(3);
    });

test('filters by organization',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Organization $organization */
        $organization = Organization::factory()->createQuietly();
        Infrastructure::factory()->for($organization)->count(2)->createQuietly();
        Infrastructure::factory()->createQuietly();

        $infrastructures = ($this->query)()->byOrganization($organization)->get();

        expect($infrastructures)->toHaveCount(2);
    });

test('filters by provider',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var CloudProvider $provider */
        $provider = CloudProvider::factory()->createQuietly();
        Infrastructure::factory()->for($provider, 'cloudProvider')->count(2)->createQuietly();
        Infrastructure::factory()->createQuietly();

        $infrastructures = ($this->query)()->byProvider($provider)->get();

        expect($infrastructures)->toHaveCount(2);
    });

test('filters by status',
    /**
     * @throws Throwable
     */
    function (): void {
        Infrastructure::factory()->createQuietly();
        Infrastructure::factory()->provisioning()->createQuietly();
        Infrastructure::factory()->degraded()->createQuietly();

        $infrastructures = ($this->query)()->byStatus(InfrastructureStatus::Degraded)->get();

        expect($infrastructures)->toHaveCount(1)
            ->and($infrastructures->first()->status)->toBe(InfrastructureStatus::Degraded);
    });

test('filters healthy infrastructures',
    /**
     * @throws Throwable
     */
    function (): void {
        Infrastructure::factory()->createQuietly();
        Infrastructure::factory()->provisioning()->createQuietly();

        $infrastructures = ($this->query)()->healthy()->get();

        expect($infrastructures)->toHaveCount(1)
            ->and($infrastructures->first()->status)->toBe(InfrastructureStatus::Healthy);
    });

test('filters provisioning infrastructures',
    /**
     * @throws Throwable
     */
    function (): void {
        Infrastructure::factory()->createQuietly();
        Infrastructure::factory()->provisioning()->createQuietly();

        $infrastructures = ($this->query)()->provisioning()->get();

        expect($infrastructures)->toHaveCount(1)
            ->and($infrastructures->first()->status)->toBe(InfrastructureStatus::Provisioning);
    });

test('searches by name and description',
    /**
     * @throws Throwable
     */
    function (): void {
        Infrastructure::factory()->createQuietly(['name' => 'Production EU', 'description' => 'Main production environment']);
        Infrastructure::factory()->createQuietly(['name' => 'Staging US', 'description' => 'Staging environment']);

        $byName = ($this->query)()->search('Production')->get();
        $byDescription = ($this->query)()->search('Staging environment')->get();

        expect($byName)->toHaveCount(1)
            ->and($byName->first()->name)->toBe('Production EU')
            ->and($byDescription)->toHaveCount(1)
            ->and($byDescription->first()->name)->toBe('Staging US');
    });

test('orders by name',
    /**
     * @throws Throwable
     */
    function (): void {
        Infrastructure::factory()->createQuietly(['name' => 'Charlie Infra']);
        Infrastructure::factory()->createQuietly(['name' => 'Alpha Infra']);
        Infrastructure::factory()->createQuietly(['name' => 'Beta Infra']);

        $infrastructures = ($this->query)()->ordered()->get();

        expect($infrastructures->pluck('name')->toArray())->toBe(['Alpha Infra', 'Beta Infra', 'Charlie Infra']);
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

        Infrastructure::factory()->for($organization)->for($provider, 'cloudProvider')->createQuietly(['name' => 'Prod']);
        Infrastructure::factory()->for($organization)->for($provider, 'cloudProvider')->provisioning()->createQuietly(['name' => 'New']);
        Infrastructure::factory()->createQuietly(['name' => 'Other Prod']);

        $infrastructures = ($this->query)()
            ->byOrganization($organization)
            ->byProvider($provider)
            ->healthy()
            ->get();

        expect($infrastructures)->toHaveCount(1)
            ->and($infrastructures->first()->name)->toBe('Prod');
    });

test('returns count',
    /**
     * @throws Throwable
     */
    function (): void {
        Infrastructure::factory()->count(4)->createQuietly();

        $count = ($this->query)()->count();

        expect($count)->toBe(4);
    });

test('paginates results',
    /**
     * @throws Throwable
     */
    function (): void {
        Infrastructure::factory()->count(20)->createQuietly();

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
        Infrastructure::factory()->createQuietly(['name' => 'Production']);
        Infrastructure::factory()->provisioning()->createQuietly(['name' => 'Staging']);

        $query1 = ($this->query)()->healthy();
        $query2 = ($this->query)()->provisioning();

        expect($query1->first()->name)->toBe('Production')
            ->and($query2->first()->name)->toBe('Staging');
    });
