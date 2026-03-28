<?php

declare(strict_types=1);

use App\Client\LarakubeClient;
use App\Exceptions\LarakubeApiException;
use Illuminate\Support\Facades\Http;

test('get sends a get request',
    /**
     * @throws Throwable
     */
    function (): void {
        Http::fake([
            '*/api/v1/test' => Http::response(['data' => 'ok']),
        ]);

        $client = new LarakubeClient(baseUrl: 'http://localhost:8000');
        $response = $client->get('/api/v1/test');

        expect($response->json('data'))->toBe('ok');
    });

test('attaches organization header when set',
    /**
     * @throws Throwable
     */
    function (): void {
        Http::fake([
            '*' => Http::response(['data' => 'ok']),
        ]);

        $client = new LarakubeClient(
            baseUrl: 'http://localhost:8000',
            organizationId: 'org-123',
        );
        $client->get('/api/v1/test');

        Http::assertSent(fn ($request): bool => $request->hasHeader('X-Organization-Id', 'org-123'));
    });

test('attaches infrastructure header when set',
    /**
     * @throws Throwable
     */
    function (): void {
        Http::fake([
            '*' => Http::response(['data' => 'ok']),
        ]);

        $client = new LarakubeClient(
            baseUrl: 'http://localhost:8000',
            infrastructureId: 'infra-456',
        );
        $client->get('/api/v1/test');

        Http::assertSent(fn ($request): bool => $request->hasHeader('X-Infrastructure-Id', 'infra-456'));
    });

test('throws on non-structured error response',
    /**
     * @throws Throwable
     */
    function (): void {
        Http::fake([
            '*' => Http::response(['message' => 'Server error'], 500),
        ]);

        $client = new LarakubeClient(baseUrl: 'http://localhost:8000');

        try {
            $client->get('/api/v1/test');
        } catch (LarakubeApiException $e) {
            expect($e->getMessage())->toBe('Server error')
                ->and($e->getCode())->toBe(401);

            return;
        }

        $this->fail('Expected LarakubeApiException was not thrown.');
    });

test('throws with default message when body has no message',
    /**
     * @throws Throwable
     */
    function (): void {
        Http::fake([
            '*' => Http::response(null, 404),
        ]);

        $client = new LarakubeClient(baseUrl: 'http://localhost:8000');

        try {
            $client->get('/api/v1/test');
        } catch (LarakubeApiException $e) {
            expect($e->getMessage())->toBe('An unexpected error occurred.')
                ->and($e->getCode())->toBe(404);

            return;
        }

        $this->fail('Expected LarakubeApiException was not thrown.');
    });
