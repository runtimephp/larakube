<?php

declare(strict_types=1);

use App\Models\Organization;
use App\Models\User;
use App\Queries\OrganizationQuery;
use Illuminate\Database\Eloquent\Builder;

beforeEach(function (): void {
    $this->query = app(OrganizationQuery::class);
});

test('returns all organizations',
    /**
     * @throws Throwable
     */
    function (): void {
        Organization::factory()->count(3)->createQuietly();

        $organizations = ($this->query)()->get();

        expect($organizations)->toHaveCount(3);
    });

test('filters by user',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->createQuietly();
        /** @var Organization $memberOrg */
        $memberOrg = Organization::factory()->createQuietly();
        $memberOrg->users()->attach($user);
        Organization::factory()->createQuietly();

        $organizations = ($this->query)()->byUser($user)->get();

        expect($organizations)->toHaveCount(1)
            ->and($organizations->first()->id)->toBe($memberOrg->id);
    });

test('searches by name and slug',
    /**
     * @throws Throwable
     */
    function (): void {
        Organization::factory()->createQuietly(['name' => 'Acme Corp', 'slug' => 'acme-corp']);
        Organization::factory()->createQuietly(['name' => 'Globex Inc', 'slug' => 'globex-inc']);

        $byName = ($this->query)()->search('Acme')->get();
        $bySlug = ($this->query)()->search('globex')->get();

        expect($byName)->toHaveCount(1)
            ->and($byName->first()->name)->toBe('Acme Corp')
            ->and($bySlug)->toHaveCount(1)
            ->and($bySlug->first()->slug)->toBe('globex-inc');
    });

test('orders by name',
    /**
     * @throws Throwable
     */
    function (): void {
        Organization::factory()->createQuietly(['name' => 'Charlie Co']);
        Organization::factory()->createQuietly(['name' => 'Alpha Co']);
        Organization::factory()->createQuietly(['name' => 'Beta Co']);

        $organizations = ($this->query)()->ordered()->get();

        expect($organizations->pluck('name')->toArray())->toBe(['Alpha Co', 'Beta Co', 'Charlie Co']);
    });

test('chains multiple filters',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->createQuietly();

        /** @var Organization $memberOrg */
        $memberOrg = Organization::factory()->createQuietly(['name' => 'Acme Corp']);
        $memberOrg->users()->attach($user);

        /** @var Organization $otherMemberOrg */
        $otherMemberOrg = Organization::factory()->createQuietly(['name' => 'Other Corp']);
        $otherMemberOrg->users()->attach($user);

        Organization::factory()->createQuietly(['name' => 'Acme External']);

        $organizations = ($this->query)()->byUser($user)->search('Acme')->get();

        expect($organizations)->toHaveCount(1)
            ->and($organizations->first()->name)->toBe('Acme Corp');
    });

test('returns count',
    /**
     * @throws Throwable
     */
    function (): void {
        Organization::factory()->count(4)->createQuietly();

        $count = ($this->query)()->count();

        expect($count)->toBe(4);
    });

test('paginates results',
    /**
     * @throws Throwable
     */
    function (): void {
        Organization::factory()->count(20)->createQuietly();

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
        Organization::factory()->createQuietly(['name' => 'Acme Corp']);
        Organization::factory()->createQuietly(['name' => 'Globex Inc']);

        $query1 = ($this->query)()->search('Acme');
        $query2 = ($this->query)()->search('Globex');

        expect($query1->first()->name)->toBe('Acme Corp')
            ->and($query2->first()->name)->toBe('Globex Inc');
    });
