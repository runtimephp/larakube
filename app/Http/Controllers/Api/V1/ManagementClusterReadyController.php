<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\MarkManagementClusterReady;
use App\Http\Requests\Api\V1\UpdateManagementClusterReadyRequest;
use App\Models\ManagementCluster;
use Illuminate\Http\JsonResponse;

final class ManagementClusterReadyController
{
    public function update(
        UpdateManagementClusterReadyRequest $request,
        ManagementCluster $managementCluster,
        MarkManagementClusterReady $markManagementClusterReady,
    ): JsonResponse {
        $markManagementClusterReady->handle($managementCluster);

        return response()->json(null, 204);
    }
}
