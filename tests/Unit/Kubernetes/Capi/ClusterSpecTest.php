<?php

declare(strict_types=1);

use App\Http\Integrations\Kubernetes\Enums\ApiVersion;
use App\Http\Integrations\Kubernetes\Enums\Kind;
use App\Http\Integrations\Kubernetes\Manifests\Capi\ClusterSpec;
use App\Http\Integrations\Kubernetes\Manifests\ObjectReference;

test('serializes with references and network',
    /**
     * @throws Throwable
     */
    function (): void {
        $spec = new ClusterSpec(
            controlPlaneRef: new ObjectReference(ApiVersion::CapiControlPlaneV1Beta1, Kind::KubeadmControlPlane, 'my-cp'),
            infrastructureRef: new ObjectReference(ApiVersion::CapiInfrastructureV1Beta1, Kind::DockerCluster, 'my-cluster'),
        );

        $array = $spec->toArray();

        expect($array['controlPlaneRef']['kind'])->toBe('KubeadmControlPlane')
            ->and($array['infrastructureRef']['kind'])->toBe('DockerCluster')
            ->and($array['clusterNetwork']['pods']['cidrBlocks'])->toBe(['192.168.0.0/16']);
    });
