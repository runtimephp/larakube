<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\StoreManagementKubeconfig;
use App\Http\Requests\Api\V1\UpdateManagementClusterKubeconfigRequest;
use App\Models\ManagementCluster;
use Illuminate\Http\JsonResponse;

final class ManagementClusterKubeconfigController
{
    public function update(
        UpdateManagementClusterKubeconfigRequest $request,
        ManagementCluster $managementCluster,
        StoreManagementKubeconfig $storeManagementKubeconfig,
    ): JsonResponse {
        $storeManagementKubeconfig->handle($managementCluster, $request->string('kubeconfig')->toString());

        return response()->json(null, 204);
    }
}
