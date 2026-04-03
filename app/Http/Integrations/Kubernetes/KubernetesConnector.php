<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes;

use App\Http\Integrations\Kubernetes\Data\StatusData;
use App\Http\Integrations\Kubernetes\Exceptions\KubernetesStatusException;
use JsonException;
use Saloon\Contracts\Authenticator;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Connector;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Plugins\AcceptsJson;
use Saloon\Traits\Plugins\AlwaysThrowOnErrors;
use SensitiveParameter;
use Throwable;

final class KubernetesConnector extends Connector
{
    use AcceptsJson;
    use AlwaysThrowOnErrors;

    public ?int $tries = 3;

    public ?int $retryInterval = 500;

    public ?bool $useExponentialBackoff = true;

    public ?bool $throwOnMaxTries = true;

    public function __construct(
        private readonly string $server,
        #[SensitiveParameter] private readonly string $token,
        private readonly bool $verifySsl = true,
    ) {}

    public function resolveBaseUrl(): string
    {
        return $this->server;
    }

    public function getRequestException(Response $response, ?Throwable $senderException): ?Throwable
    {
        try {
            $body = $response->json();
        } catch (JsonException) {
            return null;
        }

        if (($body['kind'] ?? null) === 'Status' && ($body['status'] ?? null) === 'Failure') {
            return new KubernetesStatusException(StatusData::fromKubernetesResponse($body));
        }

        return null;
    }

    public function handleRetry(FatalRequestException|RequestException $exception, Request $request): bool
    {
        if ($exception instanceof FatalRequestException) {
            return true;
        }

        $status = $exception->getResponse()->status();

        return in_array($status, [429, 503], true);
    }

    protected function defaultAuth(): Authenticator
    {
        return new TokenAuthenticator($this->token);
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultConfig(): array
    {
        return [
            'verify' => $this->verifySsl,
            'timeout' => 30,
        ];
    }
}
