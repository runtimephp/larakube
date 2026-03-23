<?php

declare(strict_types=1);

namespace App\Services\CloudProviders;

use App\Contracts\CloudProviderClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

final class DigitalOceanClient implements CloudProviderClient
{
    /**
     * @throws ConnectionException
     */
    public function validateToken(#[\SensitiveParameter] string $token): bool
    {
        $response = Http::withToken($token)
            ->get('https://api.digitalocean.com/v2/account');

        return $response->successful();
    }
}
