<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\StoreManagementKubeconfig;
use App\Http\Requests\Api\V1\StoreKubeconfigRequest;
use App\Models\ManagementCluster;
use Illuminate\Http\JsonResponse;

final class ManagementClusterKubeconfigController
{
    public function update(
        ManagementCluster $managementCluster,
        StoreKubeconfigRequest $request,
        StoreManagementKubeconfig $storeManagementKubeconfig,
    ): JsonResponse {
        $storeManagementKubeconfig->handle($managementCluster, $request->validated('kubeconfig'));

        return response()->json(null, 204);
    }
}
