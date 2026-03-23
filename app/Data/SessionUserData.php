<?php

declare(strict_types=1);

namespace App\Data;

final readonly class SessionUserData
{
    public function __construct(
        public string $id,
        public string $name,
        public string $email,
        #[\SensitiveParameter]
        public string $token,
    ) {}

    /** @param array{id: string, name: string, email: string, token: string} $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            email: $data['email'],
            token: $data['token'],
        );
    }

    /** @return array{id: string, name: string, email: string, token: string} */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'token' => $this->token,
        ];
    }
}
