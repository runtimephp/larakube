<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\CreateManagementCluster;
use App\Actions\DeleteManagementCluster;
use App\Data\CreateManagementClusterData;
use App\Http\Requests\Api\V1\DestroyManagementClusterRequest;
use App\Http\Requests\Api\V1\IndexManagementClusterRequest;
use App\Http\Requests\Api\V1\ShowManagementClusterRequest;
use App\Http\Requests\Api\V1\StoreManagementClusterRequest;
use App\Http\Resources\ManagementClusterResource;
use App\Models\ManagementCluster;
use App\Queries\ManagementClusterQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class ManagementClusterController
{
    public function index(
        IndexManagementClusterRequest $request,
        ManagementClusterQuery $managementClusterQuery,
    ): AnonymousResourceCollection {
        $query = ($managementClusterQuery)();

        return ManagementClusterResource::collection($query->get());
    }

    public function store(
        StoreManagementClusterRequest $request,
        CreateManagementCluster $createManagementCluster,
    ): JsonResponse {
        $cluster = $createManagementCluster->handle(
            new CreateManagementClusterData(
                name: $request->string('name')->toString(),
                providerId: $request->string('provider_id')->toString(),
                platformRegionId: $request->string('platform_region_id')->toString(),
                version: $request->string('version')->toString(),
            ),
        );

        return new ManagementClusterResource($cluster)
            ->response()
            ->setStatusCode(201);
    }

    public function show(
        ShowManagementClusterRequest $request,
        ManagementCluster $managementCluster,
    ): ManagementClusterResource {
        return new ManagementClusterResource($managementCluster);
    }

    public function destroy(
        DestroyManagementClusterRequest $request,
        ManagementCluster $managementCluster,
        DeleteManagementCluster $deleteManagementCluster,
    ): JsonResponse {
        $deleteManagementCluster->handle($managementCluster);

        return response()->json(null, 204);
    }
}
