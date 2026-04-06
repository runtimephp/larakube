<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\IndexProviderRequest;
use App\Http\Requests\Admin\ShowProviderRequest;
use App\Http\Resources\PlatformRegionResource;
use App\Http\Resources\ProviderResource;
use App\Models\Provider;
use App\Queries\ProviderQuery;
use Inertia\Inertia;
use Inertia\Response;

final class ProviderController
{
    public function index(IndexProviderRequest $request, ProviderQuery $query): Response
    {
        $providers = ($query)()->orderBy()->get();

        return Inertia::render('admin/providers/index', [
            'providers' => ProviderResource::collection($providers)->resolve(),
        ]);
    }

    public function show(ShowProviderRequest $request, Provider $provider): Response
    {
        return Inertia::render('admin/providers/show', [
            'provider' => ProviderResource::make($provider)->resolve(),
            'regions' => PlatformRegionResource::collection($provider->regions)->resolve(),
        ]);
    }
}
