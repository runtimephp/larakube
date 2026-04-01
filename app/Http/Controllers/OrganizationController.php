<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CreateOrganization;
use App\Data\CreateOrganizationData;
use App\Rules\OrganizationName;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class OrganizationController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('organizations/create');
    }

    public function store(Request $request, CreateOrganization $createOrganization): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', new OrganizationName],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $organization = $createOrganization->handle(
            new CreateOrganizationData(
                name: $validated['name'],
                description: $validated['description'] ?? null,
            ),
            owner: $request->user(),
        );

        return redirect("/{$organization->slug}/dashboard");
    }
}
