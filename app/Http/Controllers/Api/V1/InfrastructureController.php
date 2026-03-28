<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\CreateInfrastructure;
use App\Data\CreateInfrastructureData;
use App\Http\Requests\Api\V1\CreateInfrastructureRequest;
use App\Http\Resources\InfrastructureResource;
use App\Queries\CloudProviderQuery;
use App\Queries\InfrastructureQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class InfrastructureController
{
    public function index(Request $request, InfrastructureQuery $infrastructureQuery): AnonymousResourceCollection
    {
        $infrastructures = ($infrastructureQuery)()
            ->byOrganization($request->organization)
            ->ordered()
            ->get();

        return InfrastructureResource::collection($infrastructures);
    }

    public function store(
        CreateInfrastructureRequest $request,
        CreateInfrastructure $createInfrastructure,
        CloudProviderQuery $cloudProviderQuery,
    ): JsonResponse {
        $provider = ($cloudProviderQuery)()
            ->byOrganization($request->organization)
            ->builder()
            ->findOrFail($request->validated('cloud_provider_id'));

        $infrastructure = $createInfrastructure->handle(
            $provider,
            new CreateInfrastructureData(
                name: $request->validated('name'),
                description: $request->validated('description'),
            ),
        )->refresh();

        return (new InfrastructureResource($infrastructure))
            ->response()
            ->setStatusCode(201);
    }
}
