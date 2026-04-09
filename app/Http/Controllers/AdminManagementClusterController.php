<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Admin\IndexManagementClusterRequest;
use App\Http\Requests\Admin\ShowManagementClusterRequest;
use App\Http\Requests\CreateManagementClusterRequest;
use App\Http\Resources\ManagementClusterResource;
use App\Http\Resources\ProviderResource;
use App\Models\ManagementCluster;
use App\Queries\ManagementClusterQuery;
use App\Queries\ProviderQuery;
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



}
