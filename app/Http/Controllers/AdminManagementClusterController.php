<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CreateManagementCluster;
use App\Data\CreateManagementClusterData;
use App\Enums\KubernetesVersion;
use App\Http\Requests\Admin\IndexManagementClusterRequest;
use App\Http\Requests\Admin\ShowManagementClusterRequest;
use App\Http\Requests\CreateManagementClusterRequest;
use App\Http\Requests\StoreManagementClusterRequest;
use App\Http\Resources\ManagementClusterResource;
use App\Http\Resources\ProviderResource;
use App\Models\ManagementCluster;
use App\Queries\ManagementClusterQuery;
use App\Queries\ProviderQuery;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class AdminManagementClusterController
{
    public function index(IndexManagementClusterRequest $request, ManagementClusterQuery $query): Response
    {
        $clusters = ($query)()->get();

        return Inertia::render('admin-management-clusters/index', [
            'clusters' => ManagementClusterResource::collection($clusters)->resolve(),
        ]);
    }

    public function show(ShowManagementClusterRequest $request, ManagementCluster $managementCluster): Response
    {
        $managementCluster->load(['provider', 'platformRegion']);

        return Inertia::render('admin-management-clusters/show', [
            'cluster' => (new ManagementClusterResource($managementCluster))->resolve(),
        ]);
    }

    public function create(
        CreateManagementClusterRequest $createManagementClusterRequest,
        ProviderQuery $providerQuery
    ): Response {

        $providers = ($providerQuery)()
            ->with(['regions'])
            ->active()
            ->get();

        return Inertia::render('admin-management-clusters/create', [
            'providers' => ProviderResource::collection($providers)->resolve(),
        ]);
    }

    public function store(
        StoreManagementClusterRequest $storeManagementClusterRequest,
        CreateManagementCluster $createManagementCluster
    ): RedirectResponse {

        $createManagementClusterData = new CreateManagementClusterData(
            name: $storeManagementClusterRequest->string('name')->toString(),
            providerId: $storeManagementClusterRequest->string('provider_id')->toString(),
            platformRegionId: $storeManagementClusterRequest->string('region_id')->toString(),
            version: KubernetesVersion::V1_35_3,
        );

        $managementCluster = $createManagementCluster->handle($createManagementClusterData);

        return redirect()->route('admin.management-clusters.show', [
            'management_cluster' => $managementCluster->id,
        ]);

    }
}
