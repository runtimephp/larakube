<?php

declare(strict_types=1);

use App\Models\User;

test('register user command creates a user',
    /**
     * @throws Throwable
     */
    function (): void {
        $this->artisan('user:register')
            ->expectsQuestion('Name', 'John Doe')
            ->expectsQuestion('Email', 'john@example.com')
            ->expectsQuestion('Password', 'password123')
            ->expectsOutputToContain('User [John Doe] registered successfully')
            ->assertSuccessful();

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
    });

test('register user command generates uuid for user id',
    /**
     * @throws Throwable
     */
    function (): void {
        $this->artisan('user:register')
            ->expectsQuestion('Name', 'Jane Doe')
            ->expectsQuestion('Email', 'jane@example.com')
            ->expectsQuestion('Password', 'password123')
            ->assertSuccessful();

        $user = User::query()->where('email', 'jane@example.com')->first();

        expect($user->id)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/');
    });
