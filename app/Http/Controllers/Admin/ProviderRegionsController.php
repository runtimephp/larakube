<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\ShowProviderRegionsRequest;
use App\Http\Resources\PlatformRegionResource;
use App\Http\Resources\ProviderResource;
use App\Models\Provider;
use Inertia\Inertia;
use Inertia\Response;

final class ProviderRegionsController
{
    public function show(ShowProviderRegionsRequest $request, Provider $provider): Response
    {
        return Inertia::render('admin/providers/regions', [
            'provider' => ProviderResource::make($provider)->resolve(),
            'regions' => PlatformRegionResource::collection($provider->regions)->resolve(),
            'can' => [
                'sync_regions' => $request->user()->can('syncRegions', $provider),
            ],
        ]);
    }
}
