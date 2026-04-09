<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\SyncProviderRegions;
use App\Http\Requests\Admin\SyncProviderRegionsRequest;
use App\Models\Provider;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;

final class ProviderRegionSyncController
{
    /**
     * @throws ConnectionException
     */
    public function store(
        SyncProviderRegionsRequest $request,
        Provider $provider,
        SyncProviderRegions $syncProviderRegions,
    ): RedirectResponse {
        if ($provider->api_token === null) {
            return redirect()->back()->withErrors([
                'provider' => 'This provider does not have an API token configured.',
            ]);
        }

        $syncProviderRegions->handle($provider);

        return redirect()->back();
    }
}
