<?php

declare(strict_types=1);

use App\Http\Integrations\Kubernetes\Enums\ApiVersion;
use App\Http\Integrations\Kubernetes\Enums\Kind;
use App\Http\Integrations\Kubernetes\Manifests\ObjectReference;

test('serializes to array',
    /**
     * @throws Throwable
     */
    function (): void {
        $ref = new ObjectReference(ApiVersion::CapiCoreV1Beta1, Kind::Cluster, 'my-cluster');

        expect($ref->toArray())->toBe([
            'apiVersion' => 'cluster.x-k8s.io/v1beta1',
            'kind' => 'Cluster',
            'name' => 'my-cluster',
        ]);
    });
