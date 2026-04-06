<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\IndexProviderRequest;
use App\Http\Requests\Admin\ShowProviderRequest;
use App\Http\Requests\Admin\UpdateProviderRequest;
use App\Http\Resources\PlatformRegionResource;
use App\Http\Resources\ProviderResource;
use App\Models\Provider;
use App\Queries\ProviderQuery;
use Illuminate\Http\RedirectResponse;
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
            'can' => [
                'update' => $request->user()->can('update', $provider),
            ],
        ]);
    }

    public function update(UpdateProviderRequest $request, Provider $provider): RedirectResponse
    {
        $data = ['is_active' => $request->boolean('is_active')];

        $apiToken = $request->string('api_token')->toString();

        if ($apiToken !== '') {
            $data['api_token'] = $apiToken;
        }

        $provider->update($data);

        return redirect()->back();
    }
}
