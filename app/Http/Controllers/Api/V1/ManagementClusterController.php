<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\CreateManagementCluster;
use App\Actions\DeleteManagementCluster;
use App\Data\CreateManagementClusterData;
use App\Http\Requests\Api\V1\CreateManagementClusterRequest;
use App\Http\Resources\ManagementClusterResource;
use App\Models\ManagementCluster;
use App\Queries\ManagementClusterQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ManagementClusterController
{
    public function store(
        CreateManagementClusterRequest $request,
        CreateManagementCluster $createManagementCluster,
    ): JsonResponse {
        $cluster = $createManagementCluster->handle(
            new CreateManagementClusterData(
                name: $request->validated('name'),
                provider: $request->validated('provider'),
                region: $request->validated('region'),
            ),
        );

        return (new ManagementClusterResource($cluster))
            ->response()
            ->setStatusCode(201);
    }

    public function show(
        Request $request,
        ManagementClusterQuery $managementClusterQuery,
    ): JsonResponse {
        $cluster = ($managementClusterQuery)()
            ->byProvider($request->query('provider'))
            ->byRegion($request->query('region'))
            ->first();

        if (! $cluster) {
            return response()->json(['message' => 'Management cluster not found.'], 404);
        }

        return (new ManagementClusterResource($cluster))
            ->response();
    }

    public function destroy(
        ManagementCluster $managementCluster,
        DeleteManagementCluster $deleteManagementCluster,
    ): JsonResponse {
        $deleteManagementCluster->handle($managementCluster);

        return response()->json(null, 204);
    }
}
