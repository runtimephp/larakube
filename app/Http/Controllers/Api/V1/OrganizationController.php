<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\CreateOrganization;
use App\Data\CreateOrganizationData;
use App\Http\Requests\Api\V1\CreateOrganizationRequest;
use App\Http\Resources\OrganizationResource;
use App\Queries\OrganizationQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class OrganizationController
{
    public function index(Request $request, OrganizationQuery $organizationQuery): AnonymousResourceCollection
    {
        $organizations = ($organizationQuery)()
            ->byUser($request->user())
            ->ordered()
            ->get();

        return OrganizationResource::collection($organizations);
    }

    public function store(CreateOrganizationRequest $request, CreateOrganization $createOrganization): JsonResponse
    {
        $organization = $createOrganization->handle(
            new CreateOrganizationData(
                name: $request->validated('name'),
                description: $request->validated('description'),
            ),
            $request->user(),
        );

        return (new OrganizationResource($organization))
            ->response()
            ->setStatusCode(201);
    }
}
