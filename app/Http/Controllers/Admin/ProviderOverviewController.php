<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\ShowProviderOverviewRequest;
use App\Http\Resources\ProviderResource;
use App\Models\Provider;
use Inertia\Inertia;
use Inertia\Response;

final class ProviderOverviewController
{
    public function show(ShowProviderOverviewRequest $request, Provider $provider): Response
    {
        return Inertia::render('admin/providers/overview', [
            'provider' => ProviderResource::make($provider)->resolve(),
            'regionsCount' => $provider->regions()->count(),
        ]);
    }
}
