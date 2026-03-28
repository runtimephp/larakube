<?php

declare(strict_types=1);

namespace App\Data;

final readonly class InfrastructureData
{
    public function __construct(
        public string $id,
        public string $name,
        public ?string $description,
        public string $status,
        public string $cloudProviderId,
    ) {}

    /**
     * @param  array{id: string, name: string, description: string|null, status: string, cloud_provider_id: string}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            description: $data['description'] ?? null,
            status: $data['status'],
            cloudProviderId: $data['cloud_provider_id'],
        );
    }

    /**
     * @return array{id: string, name: string, description: string|null, status: string, cloud_provider_id: string}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'cloud_provider_id' => $this->cloudProviderId,
        ];
    }
}
