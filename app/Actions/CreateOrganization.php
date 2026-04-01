<?php

declare(strict_types=1);

namespace App\Actions;

use App\Data\CreateOrganizationData;
use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class CreateOrganization
{
    public function __construct(
        private SwitchOrganization $switchOrganization,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(CreateOrganizationData $createOrganizationData, ?User $owner = null): Organization
    {
        return DB::transaction(function () use ($createOrganizationData, $owner): Organization {
            $organization = Organization::query()->create([
                'name' => $createOrganizationData->name,
                'description' => $createOrganizationData->description,
            ]);

            if ($owner instanceof User) {
                $organization->users()->attach($owner, ['role' => OrganizationRole::Owner]);
                $this->switchOrganization->handle($owner, $organization);
            }

            return $organization;
        });
    }
}
