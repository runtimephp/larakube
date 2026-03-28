<?php

declare(strict_types=1);

use App\Models\User;
use App\Queries\UserQuery;
use Illuminate\Database\Eloquent\Builder;

beforeEach(function (): void {
    $this->query = app(UserQuery::class);
});

test('returns all users',
    /**
     * @throws Throwable
     */
    function (): void {
        User::factory()->count(3)->createQuietly();

        $users = ($this->query)()->get();

        expect($users)->toHaveCount(3);
    });

test('filters by email',
    /**
     * @throws Throwable
     */
    function (): void {
        User::factory()->createQuietly(['email' => 'john@example.com']);
        User::factory()->createQuietly(['email' => 'jane@example.com']);

        /** @var User $found */
        $found = ($this->query)()->byEmail('john@example.com')->first();

        expect($found)->not->toBeNull()
            ->and($found->email)->toBe('john@example.com');
    });

test('filters verified users',
    /**
     * @throws Throwable
     */
    function (): void {
        User::factory()->createQuietly();
        User::factory()->unverified()->createQuietly();

        $users = ($this->query)()->verified()->get();

        expect($users)->toHaveCount(1)
            ->and($users->first()->email_verified_at)->not->toBeNull();
    });

test('filters unverified users',
    /**
     * @throws Throwable
     */
    function (): void {
        User::factory()->createQuietly();
        User::factory()->unverified()->createQuietly();

        $users = ($this->query)()->unverified()->get();

        expect($users)->toHaveCount(1)
            ->and($users->first()->email_verified_at)->toBeNull();
    });

test('searches by name and email',
    /**
     * @throws Throwable
     */
    function (): void {
        User::factory()->createQuietly(['name' => 'John Doe', 'email' => 'john@example.com']);
        User::factory()->createQuietly(['name' => 'Jane Smith', 'email' => 'jane@example.com']);

        $byName = ($this->query)()->search('John')->get();
        $byEmail = ($this->query)()->search('jane@')->get();

        expect($byName)->toHaveCount(1)
            ->and($byName->first()->name)->toBe('John Doe')
            ->and($byEmail)->toHaveCount(1)
            ->and($byEmail->first()->name)->toBe('Jane Smith');
    });

test('orders by name',
    /**
     * @throws Throwable
     */
    function (): void {
        User::factory()->createQuietly(['name' => 'Charlie']);
        User::factory()->createQuietly(['name' => 'Alice']);
        User::factory()->createQuietly(['name' => 'Bob']);

        $users = ($this->query)()->ordered()->get();

        expect($users->pluck('name')->toArray())->toBe(['Alice', 'Bob', 'Charlie']);
    });

test('chains multiple filters',
    /**
     * @throws Throwable
     */
    function (): void {
        User::factory()->createQuietly(['name' => 'John Doe', 'email' => 'john@example.com']);
        User::factory()->unverified()->createQuietly(['name' => 'Jane Doe', 'email' => 'jane@example.com']);

        $users = ($this->query)()->verified()->search('Doe')->get();

        expect($users)->toHaveCount(1)
            ->and($users->first()->name)->toBe('John Doe');
    });

test('returns count',
    /**
     * @throws Throwable
     */
    function (): void {
        User::factory()->count(5)->createQuietly();

        $count = ($this->query)()->count();

        expect($count)->toBe(5);
    });

test('paginates results',
    /**
     * @throws Throwable
     */
    function (): void {
        User::factory()->count(20)->createQuietly();

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
        User::factory()->createQuietly(['email' => 'john@example.com']);
        User::factory()->createQuietly(['email' => 'jane@example.com']);

        $query1 = ($this->query)()->byEmail('john@example.com');
        $query2 = ($this->query)()->byEmail('jane@example.com');

        expect($query1->first()->email)->toBe('john@example.com')
            ->and($query2->first()->email)->toBe('jane@example.com');
    });
