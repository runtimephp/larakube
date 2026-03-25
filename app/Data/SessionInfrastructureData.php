<?php

declare(strict_types=1);

namespace App\Data;

final readonly class SessionInfrastructureData
{
    public function __construct(
        public string $id,
        public string $name,
    ) {}

    /** @param array{id: string, name: string} $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
        );
    }

    /** @return array{id: string, name: string} */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
