<?php

declare(strict_types=1);

namespace App\Services\Hetzner;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use SensitiveParameter;

class HetznerService
{
    private const string BASE_URL = 'https://api.hetzner.cloud/v1';

    /**
     * @throws ConnectionException
     */
    public function validateToken(#[SensitiveParameter] string $token): bool
    {
        $response = Http::withToken($token)
            ->get(self::BASE_URL.'/datacenters');

        return $response->successful();
    }
}
