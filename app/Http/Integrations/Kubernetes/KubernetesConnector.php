<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes;

use Saloon\Contracts\Authenticator;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;

final class KubernetesConnector extends Connector
{
    use AcceptsJson;

    public function __construct(
        private readonly string $server,
        private readonly string $token,
        private readonly bool $verifySsl = true,
    ) {}

    public function resolveBaseUrl(): string
    {
        return $this->server;
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
