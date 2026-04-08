<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\IndexManagementClusterRequest;
use App\Http\Requests\Admin\ShowManagementClusterRequest;
use App\Http\Resources\ManagementClusterResource;
use App\Models\ManagementCluster;
use App\Queries\ManagementClusterQuery;
use Inertia\Inertia;
use Inertia\Response;

final class ManagementClusterController
{
    public function index(IndexManagementClusterRequest $request, ManagementClusterQuery $query): Response
    {
        $clusters = ($query)()->get();

        return Inertia::render('admin/management-clusters/index', [
            'clusters' => ManagementClusterResource::collection($clusters)->resolve(),
        ]);
    }

    public function show(ShowManagementClusterRequest $request, ManagementCluster $managementCluster): Response
    {
        $managementCluster->load(['provider', 'platformRegion']);

        return Inertia::render('admin/management-clusters/show', [
            'cluster' => (new ManagementClusterResource($managementCluster))->resolve(),
        ]);
    }
}
