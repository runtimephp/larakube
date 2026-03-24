<?php

declare(strict_types=1);

namespace App\Services\DigitalOcean;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use SensitiveParameter;

class DigitalOceanService
{
    private const string BASE_URL = 'https://api.digitalocean.com/v2';

    /**
     * @throws ConnectionException
     */
    public function validateToken(#[SensitiveParameter] string $token): bool
    {
        $response = Http::withToken($token)
            ->get(self::BASE_URL.'/account');

        return $response->successful();
    }
}
