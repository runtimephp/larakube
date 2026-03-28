<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\SyncServers;
use App\Http\Requests\Api\V1\SyncServersRequest;
use App\Queries\CloudProviderQuery;
use Illuminate\Http\JsonResponse;

final class SyncServerController
{
    public function store(
        SyncServersRequest $request,
        SyncServers $syncServers,
        CloudProviderQuery $cloudProviderQuery,
    ): JsonResponse {
        $provider = ($cloudProviderQuery)()
            ->byOrganization($request->organization)
            ->builder()
            ->findOrFail($request->validated('cloud_provider_id'));

        $summary = $syncServers->handle($provider, $request->infrastructure);

        return response()->json(['data' => $summary->toArray()]);
    }
}
