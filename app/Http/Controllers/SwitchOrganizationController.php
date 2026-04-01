<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\SwitchOrganization;
use App\Models\Organization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class SwitchOrganizationController extends Controller
{
    public function store(Request $request, Organization $organization, SwitchOrganization $switchOrganization): RedirectResponse
    {
        $switchOrganization->handle($request->user(), $organization);

        return redirect("/{$organization->slug}/dashboard");
    }
}
