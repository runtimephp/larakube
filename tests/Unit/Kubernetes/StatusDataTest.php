<?php

declare(strict_types=1);

use App\Http\Integrations\Kubernetes\Data\StatusData;
use App\Http\Integrations\Kubernetes\Enums\StatusReason;
use App\Http\Integrations\Kubernetes\Exceptions\KubernetesStatusException;

it('parses a kubernetes status response', function (): void {
    $data = StatusData::fromKubernetesResponse([
        'apiVersion' => 'v1',
        'kind' => 'Status',
        'metadata' => [],
        'status' => 'Failure',
        'message' => 'deployments.apps "kuven-api" already exists',
        'reason' => 'AlreadyExists',
        'details' => [
            'name' => 'kuven-api',
            'group' => 'apps',
            'kind' => 'deployments',
        ],
        'code' => 409,
    ]);

    expect($data->message)->toBe('deployments.apps "kuven-api" already exists')
        ->and($data->reason)->toBe(StatusReason::AlreadyExists)
        ->and($data->code)->toBe(409)
        ->and($data->group)->toBe('apps')
        ->and($data->resource)->toBe('deployments');
});

it('falls back to unknown for unrecognized status reasons', function (): void {
    $data = StatusData::fromKubernetesResponse([
        'message' => 'something unexpected',
        'reason' => 'SomeFutureReason',
        'code' => 500,
    ]);

    expect($data->reason)->toBe(StatusReason::Unknown)
        ->and($data->group)->toBeNull()
        ->and($data->resource)->toBeNull();
});

it('wraps status data in a kubernetes status exception', function (): void {
    $status = StatusData::fromKubernetesResponse([
        'message' => 'namespaces "kuven-ns" not found',
        'reason' => 'NotFound',
        'code' => 404,
    ]);

    $exception = new KubernetesStatusException($status);

    expect($exception->getMessage())->toBe('namespaces "kuven-ns" not found')
        ->and($exception->getCode())->toBe(404)
        ->and($exception->status)->toBeInstanceOf(StatusData::class)
        ->and($exception->status->reason)->toBe(StatusReason::NotFound);
});
