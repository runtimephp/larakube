<?php

declare(strict_types=1);

namespace App\Services\CloudProviders;

use App\Contracts\CloudProviderClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

final class HetznerClient implements CloudProviderClient
{
    /**
     * @throws ConnectionException
     */
    public function validateToken(#[\SensitiveParameter] string $token): bool
    {
        $response = Http::withToken($token)
            ->get('https://api.hetzner.cloud/v1/datacenters');

        return $response->successful();
    }
}
