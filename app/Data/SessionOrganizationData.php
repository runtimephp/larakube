<?php

declare(strict_types=1);

namespace App\Data;

final readonly class SessionOrganizationData
{
    public function __construct(
        public string $id,
        public string $name,
        public string $slug,
    ) {}

    /** @param array{id: string, name: string, slug: string} $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            slug: $data['slug'],
        );
    }

    /** @return array{id: string, name: string, slug: string} */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
        ];
    }
}
