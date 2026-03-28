<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\CreateCloudProvider;
use App\Data\CreateCloudProviderData;
use App\Enums\ApiErrorCode;
use App\Enums\CloudProviderType;
use App\Http\Requests\Api\V1\CreateCloudProviderRequest;
use App\Http\Resources\CloudProviderResource;
use App\Models\CloudProvider;
use App\Queries\CloudProviderQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use RuntimeException;

final class CloudProviderController
{
    public function index(Request $request, CloudProviderQuery $cloudProviderQuery): AnonymousResourceCollection
    {
        $providers = ($cloudProviderQuery)()
            ->byOrganization($request->organization)
            ->ordered()
            ->get();

        return CloudProviderResource::collection($providers);
    }

    public function store(CreateCloudProviderRequest $request, CreateCloudProvider $createCloudProvider): JsonResponse
    {
        try {
            $cloudProvider = $createCloudProvider->handle(
                new CreateCloudProviderData(
                    name: $request->validated('name'),
                    type: CloudProviderType::from($request->validated('type')),
                    apiToken: $request->validated('api_token'),
                ),
                $request->organization,
            );
        } catch (RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'code' => ApiErrorCode::ValidationFailed->value,
                'errors' => [],
            ], ApiErrorCode::ValidationFailed->httpStatus());
        }

        return (new CloudProviderResource($cloudProvider))
            ->response()
            ->setStatusCode(201);
    }

    public function destroy(Request $request, CloudProvider $cloudProvider): Response
    {
        $cloudProvider->delete();

        return response()->noContent();
    }
}
