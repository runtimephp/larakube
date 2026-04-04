<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\MarkManagementClusterReady;
use App\Models\ManagementCluster;
use Illuminate\Http\JsonResponse;

final class ManagementClusterReadyController
{
    public function update(
        ManagementCluster $managementCluster,
        MarkManagementClusterReady $markManagementClusterReady,
    ): JsonResponse {
        $markManagementClusterReady->handle($managementCluster);

        return response()->json(null, 204);
    }
}
