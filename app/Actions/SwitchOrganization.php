<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\URL;

final class SwitchOrganization
{
    public function handle(User $user, Organization $organization): void
    {
        $user->update(['current_organization_id' => $organization->id]);
        $user->fresh('currentOrganization');

        URL::defaults(['organization' => $organization->slug]);
    }
}
