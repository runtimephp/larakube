<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CreateOrganization;
use App\Data\CreateOrganizationData;
use App\Http\Requests\StoreOrganizationRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class OrganizationController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('organizations/create');
    }

    public function store(StoreOrganizationRequest $request, CreateOrganization $createOrganization): RedirectResponse
    {
        $organization = $createOrganization->handle(
            new CreateOrganizationData(
                name: $request->validated('name'),
                description: $request->validated('description'),
            ),
            owner: $request->user(),
        );

        return redirect("/{$organization->slug}/dashboard");
    }
}
