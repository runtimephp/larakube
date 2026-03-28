<?php

declare(strict_types=1);

namespace App\Data;

final readonly class UserData
{
    public function __construct(
        public string $id,
        public string $name,
        public string $email,
    ) {}

    /**
     * @param  array{id: string, name: string, email: string}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            email: $data['email'],
        );
    }

    /**
     * @return array{id: string, name: string, email: string}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
        ];
    }
}
