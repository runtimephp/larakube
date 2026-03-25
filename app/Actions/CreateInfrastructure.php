<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\CreateInfrastructureData;
use App\Models\CloudProvider;
use App\Models\Infrastructure;

final readonly class CreateInfrastructure
{
    public function handle(CloudProvider $provider, CreateInfrastructureData $data): Infrastructure
    {
        return $provider->infrastructures()->create([
            'organization_id' => $provider->organization_id,
            'name' => $data->name,
            'description' => $data->description,
        ]);
    }
}
