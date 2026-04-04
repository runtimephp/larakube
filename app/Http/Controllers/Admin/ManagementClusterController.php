<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\IndexManagementClusterRequest;
use Inertia\Inertia;
use Inertia\Response;

final class ManagementClusterController
{
    public function index(IndexManagementClusterRequest $request): Response
    {
        return Inertia::render('admin/management-clusters/index');
    }
}
