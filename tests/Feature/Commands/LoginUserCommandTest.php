<?php

declare(strict_types=1);

use App\Models\User;

test('login command authenticates user successfully',
    /**
     * @throws Throwable
     */
    function (): void {
        User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $this->artisan('user:login')
            ->expectsQuestion('Email', 'john@example.com')
            ->expectsQuestion('Password', 'password123')
            ->expectsOutputToContain('Logged in as [John Doe]')
            ->assertSuccessful();
    });

test('login command fails with invalid credentials',
    /**
     * @throws Throwable
     */
    function (): void {
        User::factory()->create([
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $this->artisan('user:login')
            ->expectsQuestion('Email', 'john@example.com')
            ->expectsQuestion('Password', 'wrong-password')
            ->expectsOutputToContain('Invalid credentials')
            ->assertFailed();
    });
