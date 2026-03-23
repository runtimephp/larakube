<?php

declare(strict_types=1);

namespace App\Actions;

use App\Cre;
use App\Data\CreateOrganizationData;
use App\Models\Organization;

final class CreateOrganization
{

    public function handle(CreateOrganizationData $createOrganizationData): Organization
    {

        return Organization::query()->create([
            'name' => $createOrganizationData->name,
            'slug' => str($createOrganizationData->name)->slug()->toString(),
            'description' => $createOrganizationData->description,
        ]);


    }

}
