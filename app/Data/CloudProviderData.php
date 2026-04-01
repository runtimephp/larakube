<?php

declare(strict_types=1);

namespace App\Data;

final readonly class CloudProviderData
{
    public function __construct(
        public string $id,
        public string $name,
        public string $type,
        public bool $isVerified,
    ) {}

    public static function fromModel(\App\Models\CloudProvider $cloudProvider): self
    {
        return new self(
            id: $cloudProvider->id,
            name: $cloudProvider->name,
            type: $cloudProvider->type->value,
            isVerified: $cloudProvider->is_verified,
        );
    }

    /**
     * @param  array{id: string, name: string, type: string, is_verified: bool}  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            type: $data['type'],
            isVerified: $data['is_verified'],
        );
    }

    /**
     * @return array{id: string, name: string, type: string, is_verified: bool}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'is_verified' => $this->isVerified,
        ];
    }
}
