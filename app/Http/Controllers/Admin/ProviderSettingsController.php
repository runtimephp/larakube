<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\ShowProviderSettingsRequest;
use App\Http\Resources\ProviderResource;
use App\Models\Provider;
use Inertia\Inertia;
use Inertia\Response;

final class ProviderSettingsController
{
    public function show(ShowProviderSettingsRequest $request, Provider $provider): Response
    {
        return Inertia::render('admin/providers/settings', [
            'provider' => ProviderResource::make($provider)->resolve(),
            'can' => [
                'update' => $request->user()->can('update', $provider),
            ],
        ]);
    }
}
