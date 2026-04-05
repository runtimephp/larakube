<?php

declare(strict_types=1);

use App\Http\Integrations\Kubernetes\Enums\ApiVersion;
use App\Http\Integrations\Kubernetes\Enums\Kind;
use App\Http\Integrations\Kubernetes\Manifests\Capi\Docker\DockerClusterManifest;
use App\Http\Integrations\Kubernetes\Manifests\ManifestMetadata;

test('serializes and exposes routing metadata',
    /**
     * @throws Throwable
     */
    function (): void {
        $manifest = new DockerClusterManifest(
            metadata: new ManifestMetadata(name: 'my-cluster', namespace: 'kuven-org-123'),
        );

        expect($manifest->apiVersion())->toBe(ApiVersion::CapiInfrastructureV1Beta1)
            ->and($manifest->kind())->toBe(Kind::DockerCluster)
            ->and($manifest->resource())->toBe('dockerclusters')
            ->and($manifest->namespace())->toBe('kuven-org-123')
            ->and($manifest->isClusterScoped())->toBeFalse()
            ->and($manifest->toArray()['apiVersion'])->toBe('infrastructure.cluster.x-k8s.io/v1beta1')
            ->and($manifest->toArray()['kind'])->toBe('DockerCluster');
    });

test('rejects missing namespace',
    /**
     * @throws Throwable
     */
    function (): void {
        expect(fn () => new DockerClusterManifest(
            metadata: new ManifestMetadata(name: 'x'),
        ))->toThrow(InvalidArgumentException::class);
    });
