<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\CreateOrganizationData;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Throwable;

final class CreateOrganization
{
    /**
     * @throws Throwable
     */
    public function handle(CreateOrganizationData $createOrganizationData, ?User $owner = null): Organization
    {
        return DB::transaction(function () use ($createOrganizationData, $owner): Organization {
            $organization = Organization::query()->create([
                'name' => $createOrganizationData->name,
                'slug' => str($createOrganizationData->name)->slug()->toString(),
                'description' => $createOrganizationData->description,
            ]);

            if ($owner !== null) {
                $organization->users()->attach($owner, ['role' => 'owner']);
            }

            return $organization;
        });
    }
}
