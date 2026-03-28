<?php

declare(strict_types=1);

use App\Models\User;

test('register creates a user and returns user data',
    /**
     * @throws Throwable
     */
    function (): void {
        $response = $this->postJson(route('api.v1.auth.register'), [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => ['id', 'name', 'email'],
            ])
            ->assertJsonPath('data.name', 'John Doe')
            ->assertJsonPath('data.email', 'john@example.com');

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
        ]);
    });

test('register validates required fields',
    /**
     * @throws Throwable
     */
    function (): void {
        $response = $this->postJson(route('api.v1.auth.register'), []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    });

test('register rejects duplicate email',
    /**
     * @throws Throwable
     */
    function (): void {
        User::factory()->create(['email' => 'john@example.com']);

        $response = $this->postJson(route('api.v1.auth.register'), [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    });
