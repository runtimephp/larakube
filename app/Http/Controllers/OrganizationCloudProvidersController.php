<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CreateCloudProvider;
use App\Data\CloudProviderData;
use App\Data\CreateCloudProviderData;
use App\Data\OrganizationData;
use App\Enums\CloudProviderType;
use App\Http\Requests\StoreCloudProviderRequest;
use App\Models\CloudProvider;
use App\Models\Organization;
use App\Queries\CloudProviderQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

final class OrganizationCloudProvidersController
{
    public function index(Request $request, Organization $organization, CloudProviderQuery $cloudProviderQuery): Response
    {
        Gate::authorize('viewAny', [CloudProvider::class, $organization]);

        $providers = ($cloudProviderQuery)()
            ->byOrganization($organization)
            ->ordered()
            ->get()
            ->map(fn (CloudProvider $provider) => CloudProviderData::fromModel($provider)->toArray());

        return Inertia::render('organization-cloud-providers/index', [
            'organization' => OrganizationData::fromModel($organization)->toArray(),
            'cloudProviders' => $providers,
            'can' => [
                'manage' => $request->user()->can('manage', [CloudProvider::class, $organization]),
            ],
        ]);
    }

    public function store(
        StoreCloudProviderRequest $request,
        Organization $organization,
        CreateCloudProvider $createCloudProvider,
    ): RedirectResponse {
        try {
            $createCloudProvider->handle(
                new CreateCloudProviderData(
                    name: $request->validated('name'),
                    type: CloudProviderType::from($request->validated('type')),
                    apiToken: $request->validated('api_token'),
                ),
                $organization,
            );
        } catch (RuntimeException $e) {
            throw ValidationException::withMessages([
                'api_token' => $e->getMessage(),
            ]);
        }

        return to_route('organizations.settings.cloud-providers', $organization)
            ->with('status', 'cloud-provider-created');
    }

    public function destroy(Request $request, Organization $organization, CloudProvider $cloudProvider): RedirectResponse
    {
        Gate::authorize('delete', $cloudProvider);

        $cloudProvider->delete();

        return to_route('organizations.settings.cloud-providers', $organization)
            ->with('status', 'cloud-provider-deleted');
    }
}
