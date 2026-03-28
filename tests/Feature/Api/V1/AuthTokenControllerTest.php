<?php

declare(strict_types=1);

use App\Models\User;

test('login returns sanctum token and user data',
    /**
     * @throws Throwable
     */
    function (): void {
        User::factory()->create([
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson(route('api.v1.auth.token.store'), [
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['id', 'name', 'email', 'token'],
            ])
            ->assertJsonPath('data.email', 'john@example.com');

        expect($response->json('data.token'))->toBeString()->not->toBeEmpty();
    });

test('login fails with invalid credentials',
    /**
     * @throws Throwable
     */
    function (): void {
        User::factory()->create([
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson(route('api.v1.auth.token.store'), [
            'email' => 'john@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertUnauthorized()
            ->assertJsonPath('code', 'invalid_credentials');
    });

test('login validates required fields',
    /**
     * @throws Throwable
     */
    function (): void {
        $response = $this->postJson(route('api.v1.auth.token.store'), []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'password']);
    });

test('logout revokes token and returns no content',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var User $user */
        $user = User::factory()->create();
        $token = $user->createToken('cli')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->deleteJson(route('api.v1.auth.token.destroy'));

        $response->assertNoContent();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    });

test('logout requires authentication',
    /**
     * @throws Throwable
     */
    function (): void {
        $response = $this->deleteJson(route('api.v1.auth.token.destroy'));

        $response->assertUnauthorized();
    });
