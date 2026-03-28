<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\CreateServer;
use App\Actions\DeleteServer;
use App\Data\CreateServerData;
use App\Enums\ApiErrorCode;
use App\Http\Requests\Api\V1\CreateServerRequest;
use App\Http\Resources\ServerResource;
use App\Models\Server;
use App\Queries\CloudProviderQuery;
use App\Queries\ServerQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use RuntimeException;

final class ServerController
{
    public function index(Request $request, ServerQuery $serverQuery): AnonymousResourceCollection
    {
        $servers = ($serverQuery)()
            ->byOrganization($request->organization)
            ->ordered()
            ->get();

        return ServerResource::collection($servers);
    }

    public function store(
        CreateServerRequest $request,
        CreateServer $createServer,
        CloudProviderQuery $cloudProviderQuery,
    ): JsonResponse {
        $provider = ($cloudProviderQuery)()
            ->byOrganization($request->organization)
            ->builder()
            ->findOrFail($request->validated('cloud_provider_id'));

        try {
            $server = $createServer->handle(
                $provider,
                new CreateServerData(
                    name: $request->validated('name'),
                    type: $request->validated('type'),
                    image: $request->validated('image'),
                    region: $request->validated('region'),
                    infrastructure_id: $request->infrastructure->id,
                ),
            );
        } catch (RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'code' => ApiErrorCode::ValidationFailed->value,
                'errors' => [],
            ], ApiErrorCode::ValidationFailed->httpStatus());
        }

        return new ServerResource($server)
            ->response()
            ->setStatusCode(201);
    }

    public function show(Server $server): ServerResource
    {
        return new ServerResource($server);
    }

    public function destroy(Server $server, DeleteServer $deleteServer): JsonResponse|Response
    {
        try {
            $deleteServer->handle($server);
        } catch (RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'code' => ApiErrorCode::ValidationFailed->value,
                'errors' => [],
            ], ApiErrorCode::ValidationFailed->httpStatus());
        }

        return response()->noContent();
    }
}
