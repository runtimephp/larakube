<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\StoreManagementSshKey;
use App\Http\Requests\Api\V1\UpdateManagementClusterSshKeyRequest;
use App\Models\ManagementCluster;
use Illuminate\Http\JsonResponse;

final class ManagementClusterSshKeyController
{
    public function update(
        UpdateManagementClusterSshKeyRequest $request,
        ManagementCluster $managementCluster,
        StoreManagementSshKey $storeManagementSshKey,
    ): JsonResponse {
        $storeManagementSshKey->handle($managementCluster, $request->string('ssh_private_key')->toString());

        return response()->json(null, 204);
    }
}
