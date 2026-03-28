<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\ApiErrorCode;
use App\Models\Infrastructure;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ResolveInfrastructure
{
    public function handle(Request $request, Closure $next): Response
    {
        $infrastructureId = $request->header('X-Infrastructure-Id');

        if ($infrastructureId === null) {
            return response()->json([
                'message' => 'The X-Infrastructure-Id header is required.',
                'code' => ApiErrorCode::ValidationFailed->value,
                'errors' => [],
            ], ApiErrorCode::ValidationFailed->httpStatus());
        }

        $infrastructure = Infrastructure::query()->find($infrastructureId);

        if ($infrastructure === null) {
            return response()->json([
                'message' => 'Infrastructure not found.',
                'code' => ApiErrorCode::NotFound->value,
                'errors' => [],
            ], ApiErrorCode::NotFound->httpStatus());
        }

        if ($infrastructure->organization_id !== $request->organization?->id) {
            return response()->json([
                'message' => 'Infrastructure does not belong to this organization.',
                'code' => ApiErrorCode::Forbidden->value,
                'errors' => [],
            ], ApiErrorCode::Forbidden->httpStatus());
        }

        $request->merge(['infrastructure' => $infrastructure]);

        return $next($request);
    }
}
