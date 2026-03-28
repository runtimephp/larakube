<?php

declare(strict_types=1);

namespace App\Data;

final readonly class OrganizationData
{
    public function __construct(
        public string $id,
        public string $name,
        public string $slug,
        public ?string $description = null,
    ) {}

    /**
     * @param  array{id: string, name: string, slug: string, description?: string|null}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            slug: $data['slug'],
            description: $data['description'] ?? null,
        );
    }

    /**
     * @return array{id: string, name: string, slug: string, description: string|null}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
        ];
    }
}
