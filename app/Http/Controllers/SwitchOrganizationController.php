<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\SwitchOrganization;
use App\Http\Requests\SwitchOrganizationRequest;
use App\Models\Organization;
use Illuminate\Http\RedirectResponse;

final class SwitchOrganizationController extends Controller
{
    public function store(SwitchOrganizationRequest $request, Organization $organization, SwitchOrganization $switchOrganization): RedirectResponse
    {
        $switchOrganization->handle($request->user(), $organization);

        return redirect("/{$organization->slug}/dashboard");
    }
}
