<?php

declare(strict_types=1);

use App\Client\HttpAuthClient;
use App\Client\LarakubeClient;
use App\Data\CreateUserData;
use App\Data\UserData;
use App\Enums\ApiErrorCode;
use App\Exceptions\LarakubeApiException;
use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    $this->larakubeClient = new LarakubeClient(
        baseUrl: 'http://localhost:8000',
    );

    $this->authClient = new HttpAuthClient($this->larakubeClient);
});

test('register returns user data',
    /**
     * @throws Throwable
     */
    function (): void {
        Http::fake([
            '*/api/v1/auth/register' => Http::response([
                'data' => [
                    'id' => 'uuid-123',
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                ],
            ], 201),
        ]);

        $result = $this->authClient->register(new CreateUserData(
            name: 'John Doe',
            email: 'john@example.com',
            password: 'password123',
        ));

        expect($result)
            ->toBeInstanceOf(UserData::class)
            ->id->toBe('uuid-123')
            ->name->toBe('John Doe')
            ->email->toBe('john@example.com');
    });

test('register throws on validation error',
    /**
     * @throws Throwable
     */
    function (): void {
        Http::fake([
            '*/api/v1/auth/register' => Http::response([
                'message' => 'Validation failed.',
                'code' => 'validation_failed',
                'errors' => ['email' => ['The email has already been taken.']],
            ], 422),
        ]);

        $this->authClient->register(new CreateUserData(
            name: 'John Doe',
            email: 'john@example.com',
            password: 'password123',
        ));
    })->throws(LarakubeApiException::class, 'Validation failed.');

test('login returns session user data with token',
    /**
     * @throws Throwable
     */
    function (): void {
        Http::fake([
            '*/api/v1/auth/token' => Http::response([
                'data' => [
                    'id' => 'uuid-123',
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                    'token' => '1|abc123',
                ],
            ]),
        ]);

        $result = $this->authClient->login('john@example.com', 'password123');

        expect($result)
            ->id->toBe('uuid-123')
            ->name->toBe('John Doe')
            ->email->toBe('john@example.com')
            ->token->toBe('1|abc123');
    });

test('login throws on invalid credentials',
    /**
     * @throws Throwable
     */
    function (): void {
        Http::fake([
            '*/api/v1/auth/token' => Http::response([
                'message' => 'Invalid credentials.',
                'code' => 'invalid_credentials',
                'errors' => [],
            ], 401),
        ]);

        try {
            $this->authClient->login('john@example.com', 'wrong');
        } catch (LarakubeApiException $e) {
            expect($e->errorData->code)->toBe(ApiErrorCode::InvalidCredentials);

            return;
        }

        $this->fail('Expected LarakubeApiException was not thrown.');
    });

test('logout sends delete request',
    /**
     * @throws Throwable
     */
    function (): void {
        $client = new LarakubeClient(
            baseUrl: 'http://localhost:8000',
            token: '1|abc123',
        );
        $authClient = new HttpAuthClient($client);

        Http::fake([
            '*/api/v1/auth/token' => Http::response(null, 204),
        ]);

        $authClient->logout();

        Http::assertSent(fn ($request): bool => $request->method() === 'DELETE'
            && str_contains((string) $request->url(), '/api/v1/auth/token')
            && $request->hasHeader('Authorization', 'Bearer 1|abc123'));
    });
