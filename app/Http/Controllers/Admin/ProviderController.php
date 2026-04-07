<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\CreateProvider;
use App\Actions\UpdateProvider;
use App\Enums\ProviderSlug;
use App\Http\Requests\Admin\IndexProviderRequest;
use App\Http\Requests\Admin\ShowProviderRequest;
use App\Http\Requests\Admin\StoreProviderRequest;
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

        $existingSlugs = $providers->pluck('slug.value')->all();

        $availableSlugs = collect(ProviderSlug::cases())
            ->reject(fn (ProviderSlug $slug) => in_array($slug->value, $existingSlugs, true))
            ->map(fn (ProviderSlug $slug) => [
                'value' => $slug->value,
                'label' => $slug->label(),
            ])
            ->values()
            ->all();

        return Inertia::render('admin/providers/index', [
            'providers' => ProviderResource::collection($providers)->resolve(),
            'availableSlugs' => $availableSlugs,
            'can' => [
                'create' => $request->user()->can('create', Provider::class),
            ],
        ]);
    }

    public function store(StoreProviderRequest $request, CreateProvider $storeProvider): RedirectResponse
    {
        $provider = $storeProvider->handle(
            $request->enum('slug', ProviderSlug::class),
            $request->string('api_token')->toString(),
        );

        return redirect()->route('admin.settings.providers.show', $provider);
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

    public function update(UpdateProviderRequest $request, Provider $provider, UpdateProvider $updateProvider): RedirectResponse
    {
        $updateProvider->handle(
            $provider,
            $request->string('api_token')->toString(),
            $request->boolean('is_active'),
        );

        return redirect()->back();
    }
}
