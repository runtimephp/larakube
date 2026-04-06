<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\IndexProviderRequest;
use App\Http\Resources\ProviderResource;
use App\Queries\ProviderQuery;
use Inertia\Inertia;
use Inertia\Response;

final class ProviderController
{
    public function index(IndexProviderRequest $request, ProviderQuery $query): Response
    {
        $providers = ($query)()->orderBy()->get();

        return Inertia::render('admin/providers/index', [
            'providers' => ProviderResource::collection($providers)->resolve(),
        ]);
    }
}
