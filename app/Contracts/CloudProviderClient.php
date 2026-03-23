<?php

declare(strict_types=1);

namespace App\Contracts;

interface CloudProviderClient
{
    public function validateToken(#[\SensitiveParameter] string $token): bool;
}
