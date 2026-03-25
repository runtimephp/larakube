<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\CloudProviderService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

final readonly class HetznerService implements CloudProviderService
{
    public function __construct(private string $token) {}

    /**
     * @throws ConnectionException
     */
    public function validateToken(): bool
    {
        $response = Http::withToken($this->token)
            ->get('https://api.hetzner.cloud/v1/datacenters');

        return $response->successful();
    }
}
