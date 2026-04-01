<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\UpdateOrganizationData;
use App\Models\Organization;

final class UpdateOrganization
{
    public function handle(Organization $organization, UpdateOrganizationData $updateOrganizationData): void
    {
        $organization->query()
            ->whereKey($organization->getKey())
            ->update([
                'name' => $updateOrganizationData->name,
                'description' => $updateOrganizationData->description,
            ]);
    }
}
