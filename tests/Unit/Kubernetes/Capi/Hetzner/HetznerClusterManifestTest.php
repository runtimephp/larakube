<?php

declare(strict_types=1);

use App\Http\Integrations\Kubernetes\Enums\ApiVersion;
use App\Http\Integrations\Kubernetes\Enums\Kind;
use App\Http\Integrations\Kubernetes\Manifests\Capi\Hetzner\HetznerClusterManifest;
use App\Http\Integrations\Kubernetes\Manifests\Capi\Hetzner\HetznerClusterSpec;
use App\Http\Integrations\Kubernetes\Manifests\ManifestMetadata;

test('serializes and exposes routing metadata',
    /**
     * @throws Throwable
     */
    function (): void {
        $manifest = new HetznerClusterManifest(
            metadata: new ManifestMetadata(name: 'prod-cluster', namespace: 'kuven-org-123'),
            spec: new HetznerClusterSpec(controlPlaneRegion: 'nuremberg', sshKeyName: 'prod-cluster'),
        );

        expect($manifest->apiVersion())->toBe(ApiVersion::CapiInfrastructureV1Beta1)
            ->and($manifest->kind())->toBe(Kind::HetznerCluster)
            ->and($manifest->resource())->toBe('hetznerclusters')
            ->and($manifest->namespace())->toBe('kuven-org-123')
            ->and($manifest->isClusterScoped())->toBeFalse()
            ->and($manifest->toArray()['spec']['controlPlaneRegion'])->toBe('nuremberg')
            ->and($manifest->toArray()['spec']['sshKeys']['hcloud'])->toBe(['prod-cluster']);
    });

test('rejects missing namespace',
    /**
     * @throws Throwable
     */
    function (): void {
        expect(fn () => new HetznerClusterManifest(
            metadata: new ManifestMetadata(name: 'x'),
            spec: new HetznerClusterSpec(controlPlaneRegion: 'nbg1', sshKeyName: 'x'),
        ))->toThrow(InvalidArgumentException::class);
    });

test('spec serializes with custom ssh key',
    /**
     * @throws Throwable
     */
    function (): void {
        $spec = new HetznerClusterSpec(
            controlPlaneRegion: 'falkenstein',
            sshKeyName: 'my-key',
        );

        expect($spec->toArray())->toBe([
            'controlPlaneRegion' => 'falkenstein',
            'sshKeys' => ['hcloud' => ['my-key']],
        ]);
    });
