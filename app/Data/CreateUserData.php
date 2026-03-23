<?php

declare(strict_types=1);

namespace App\Data;

use SensitiveParameter;

final readonly class CreateUserData
{
    public function __construct(
        public string $name,
        public string $email,
        #[SensitiveParameter]
        public string $password,
    ) {}
}
