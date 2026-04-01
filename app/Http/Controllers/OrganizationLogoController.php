<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\UpdateOrganizationLogo;
use App\Http\Requests\UpdateOrganizationLogoRequest;
use App\Models\Organization;
use Illuminate\Http\RedirectResponse;

final class OrganizationLogoController extends Controller
{
    public function update(
        UpdateOrganizationLogoRequest $request,
        Organization $organization,
        UpdateOrganizationLogo $updateOrganizationLogo,
    ): RedirectResponse {
        $updateOrganizationLogo->handle($organization, $request->file('logo'));

        return to_route('organizations.settings.general.edit', $organization)
            ->with('status', 'organization-logo-updated');
    }
}
