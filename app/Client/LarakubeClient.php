<?php

declare(strict_types=1);

namespace App\Client;

use App\Data\ApiErrorData;
use App\Enums\ApiErrorCode;
use App\Exceptions\LarakubeApiException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

final readonly class LarakubeClient
{
    public function __construct(
        private string $baseUrl,
        private ?string $token = null,
        private ?string $organizationId = null,
        private ?string $infrastructureId = null,
    ) {}

    public function get(string $path): Response
    {
        return $this->handleResponse(
            $this->request()->get($this->url($path))
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function post(string $path, array $data = []): Response
    {
        return $this->handleResponse(
            $this->request()->post($this->url($path), $data)
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function patch(string $path, array $data = []): Response
    {
        return $this->handleResponse(
            $this->request()->patch($this->url($path), $data)
        );
    }

    public function delete(string $path): Response
    {
        return $this->handleResponse(
            $this->request()->delete($this->url($path))
        );
    }

    private function request(): PendingRequest
    {
        $request = Http::acceptJson();

        if ($this->token !== null) {
            $request = $request->withToken($this->token);
        }

        if ($this->organizationId !== null) {
            $request = $request->withHeaders(['X-Organization-Id' => $this->organizationId]);
        }

        if ($this->infrastructureId !== null) {
            $request = $request->withHeaders(['X-Infrastructure-Id' => $this->infrastructureId]);
        }

        return $request;
    }

    private function url(string $path): string
    {
        return mb_rtrim($this->baseUrl, '/').'/'.mb_ltrim($path, '/');
    }

    /**
     * @throws LarakubeApiException
     */
    private function handleResponse(Response $response): Response
    {
        if ($response->successful()) {
            return $response;
        }

        $body = $response->json();

        if (is_array($body) && isset($body['code']) && ApiErrorCode::tryFrom($body['code']) !== null) {
            throw new LarakubeApiException(ApiErrorData::fromArray($body));
        }

        throw new LarakubeApiException(new ApiErrorData(
            message: $body['message'] ?? 'An unexpected error occurred.',
            code: $this->mapStatusToCode($response->status()),
        ));
    }

    private function mapStatusToCode(int $status): ApiErrorCode
    {
        return match (true) {
            $status === 401 => ApiErrorCode::Unauthenticated,
            $status === 404 => ApiErrorCode::NotFound,
            $status === 422 => ApiErrorCode::ValidationFailed,
            default => ApiErrorCode::Unauthenticated,
        };
    }
}
