<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\ApiErrorCode;
use App\Models\Organization;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ResolveOrganization
{
    public function handle(Request $request, Closure $next): Response
    {
        $organizationId = $request->header('X-Organization-Id');

        if ($organizationId === null) {
            return response()->json([
                'message' => 'The X-Organization-Id header is required.',
                'code' => ApiErrorCode::ValidationFailed->value,
                'errors' => [],
            ], ApiErrorCode::ValidationFailed->httpStatus());
        }

        $organization = Organization::query()->find($organizationId);

        if ($organization === null) {
            return response()->json([
                'message' => 'Organization not found.',
                'code' => ApiErrorCode::NotFound->value,
                'errors' => [],
            ], ApiErrorCode::NotFound->httpStatus());
        }

        if (! $request->user()->organizations()->where('organizations.id', $organization->id)->exists()) {
            return response()->json([
                'message' => 'You are not a member of this organization.',
                'code' => ApiErrorCode::Forbidden->value,
                'errors' => [],
            ], ApiErrorCode::Forbidden->httpStatus());
        }

        $request->merge(['organization' => $organization]);

        return $next($request);
    }
}
