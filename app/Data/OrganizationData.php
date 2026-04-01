<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\Organization;

final readonly class OrganizationData
{
    public function __construct(
        public string $id,
        public string $name,
        public string $slug,
        public ?string $description = null,
        public ?string $logo = null,
    ) {}

    public static function fromModel(Organization $organization): self
    {
        return new self(
            id: $organization->id,
            name: $organization->name,
            slug: $organization->slug,
            description: $organization->description,
            logo: $organization->logo,
        );
    }

    /**
     * @param  array{id: string, name: string, slug: string, description?: string|null, logo?: string|null}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            slug: $data['slug'],
            description: $data['description'] ?? null,
            logo: $data['logo'] ?? null,
        );
    }

    /**
     * @return array{id: string, name: string, slug: string, description: string|null, logo: string|null}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'logo' => $this->logo,
        ];
    }
}
