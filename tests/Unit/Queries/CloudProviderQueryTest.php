<?php

declare(strict_types=1);

use App\Enums\CloudProviderType;
use App\Models\CloudProvider;
use App\Models\Organization;
use App\Queries\CloudProviderQuery;
use Illuminate\Database\Eloquent\Builder;

beforeEach(function (): void {
    $this->query = app(CloudProviderQuery::class);
});

test('returns all cloud providers',
    /**
     * @throws Throwable
     */
    function (): void {
        CloudProvider::factory()->count(3)->createQuietly();

        $providers = ($this->query)()->get();

        expect($providers)->toHaveCount(3);
    });

test('filters by organization',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Organization $organization */
        $organization = Organization::factory()->createQuietly();
        CloudProvider::factory()->for($organization)->count(2)->createQuietly();
        CloudProvider::factory()->createQuietly();

        $providers = ($this->query)()->byOrganization($organization)->get();

        expect($providers)->toHaveCount(2);
    });

test('filters by type',
    /**
     * @throws Throwable
     */
    function (): void {
        CloudProvider::factory()->hetzner()->createQuietly();
        CloudProvider::factory()->digitalOcean()->createQuietly();

        $providers = ($this->query)()->byType(CloudProviderType::Hetzner)->get();

        expect($providers)->toHaveCount(1)
            ->and($providers->first()->type)->toBe(CloudProviderType::Hetzner);
    });

test('filters verified providers',
    /**
     * @throws Throwable
     */
    function (): void {
        CloudProvider::factory()->createQuietly();
        CloudProvider::factory()->unverified()->createQuietly();

        $providers = ($this->query)()->verified()->get();

        expect($providers)->toHaveCount(1)
            ->and($providers->first()->is_verified)->toBeTrue();
    });

test('filters unverified providers',
    /**
     * @throws Throwable
     */
    function (): void {
        CloudProvider::factory()->createQuietly();
        CloudProvider::factory()->unverified()->createQuietly();

        $providers = ($this->query)()->unverified()->get();

        expect($providers)->toHaveCount(1)
            ->and($providers->first()->is_verified)->toBeFalse();
    });

test('searches by name',
    /**
     * @throws Throwable
     */
    function (): void {
        CloudProvider::factory()->createQuietly(['name' => 'Production Hetzner']);
        CloudProvider::factory()->createQuietly(['name' => 'Staging DO']);

        $providers = ($this->query)()->search('Production')->get();

        expect($providers)->toHaveCount(1)
            ->and($providers->first()->name)->toBe('Production Hetzner');
    });

test('orders by name',
    /**
     * @throws Throwable
     */
    function (): void {
        CloudProvider::factory()->createQuietly(['name' => 'Charlie Cloud']);
        CloudProvider::factory()->createQuietly(['name' => 'Alpha Cloud']);
        CloudProvider::factory()->createQuietly(['name' => 'Beta Cloud']);

        $providers = ($this->query)()->ordered()->get();

        expect($providers->pluck('name')->toArray())->toBe(['Alpha Cloud', 'Beta Cloud', 'Charlie Cloud']);
    });

test('chains multiple filters',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Organization $organization */
        $organization = Organization::factory()->createQuietly();

        CloudProvider::factory()->for($organization)->hetzner()->createQuietly(['name' => 'Hetzner Prod']);
        CloudProvider::factory()->for($organization)->digitalOcean()->createQuietly(['name' => 'DO Prod']);
        CloudProvider::factory()->hetzner()->createQuietly(['name' => 'Hetzner Other']);

        $providers = ($this->query)()
            ->byOrganization($organization)
            ->byType(CloudProviderType::Hetzner)
            ->get();

        expect($providers)->toHaveCount(1)
            ->and($providers->first()->name)->toBe('Hetzner Prod');
    });

test('returns count',
    /**
     * @throws Throwable
     */
    function (): void {
        CloudProvider::factory()->count(5)->createQuietly();

        $count = ($this->query)()->count();

        expect($count)->toBe(5);
    });

test('paginates results',
    /**
     * @throws Throwable
     */
    function (): void {
        CloudProvider::factory()->count(20)->createQuietly();

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
        CloudProvider::factory()->hetzner()->createQuietly();
        CloudProvider::factory()->digitalOcean()->createQuietly();

        $query1 = ($this->query)()->byType(CloudProviderType::Hetzner);
        $query2 = ($this->query)()->byType(CloudProviderType::DigitalOcean);

        expect($query1->first()->type)->toBe(CloudProviderType::Hetzner)
            ->and($query2->first()->type)->toBe(CloudProviderType::DigitalOcean);
    });
