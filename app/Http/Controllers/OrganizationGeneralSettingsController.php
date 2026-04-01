<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\UpdateOrganization;
use App\Data\OrganizationData;
use App\Data\UpdateOrganizationData;
use App\Http\Requests\EditOrganizationGeneralSettingsRequest;
use App\Http\Requests\UpdateOrganizationGeneralSettingsRequest;
use App\Models\Organization;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class OrganizationGeneralSettingsController extends Controller
{
    public function edit(EditOrganizationGeneralSettingsRequest $request, Organization $organization): Response
    {
        return Inertia::render('organization-general-settings/edit', [
            'organization' => OrganizationData::fromModel($organization)->toArray(),
            'can' => [
                'update' => $request->user()->can('updateSettings', $organization),
            ],
            'status' => $request->session()->get('status'),
        ]);
    }

    public function update(
        UpdateOrganizationGeneralSettingsRequest $request,
        Organization $organization,
        UpdateOrganization $updateOrganization,
    ): RedirectResponse {
        $updateOrganization->handle($organization, new UpdateOrganizationData(
            name: $request->validated('name'),
            description: $request->validated('description'),
        ));

        return to_route('organizations.settings.general.edit', $organization)
            ->with('status', 'organization-general-settings-updated');
    }
}
