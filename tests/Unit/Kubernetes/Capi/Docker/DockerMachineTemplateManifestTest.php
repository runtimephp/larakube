<?php

declare(strict_types=1);

use App\Http\Integrations\Kubernetes\Enums\ApiVersion;
use App\Http\Integrations\Kubernetes\Enums\Kind;
use App\Http\Integrations\Kubernetes\Manifests\Capi\Docker\DockerMachineTemplateManifest;
use App\Http\Integrations\Kubernetes\Manifests\Capi\Docker\DockerMachineTemplateSpec;
use App\Http\Integrations\Kubernetes\Manifests\ManifestMetadata;

test('serializes and exposes routing metadata',
    /**
     * @throws Throwable
     */
    function (): void {
        $manifest = new DockerMachineTemplateManifest(
            metadata: new ManifestMetadata(name: 'my-cp', namespace: 'kuven-org-123'),
        );

        expect($manifest->apiVersion())->toBe(ApiVersion::CapiInfrastructureV1Beta1)
            ->and($manifest->kind())->toBe(Kind::DockerMachineTemplate)
            ->and($manifest->resource())->toBe('dockermachinetemplates')
            ->and($manifest->namespace())->toBe('kuven-org-123')
            ->and($manifest->isClusterScoped())->toBeFalse()
            ->and($manifest->toArray()['spec']['template']['spec']['extraMounts'])->toBeArray();
    });

test('rejects missing namespace',
    /**
     * @throws Throwable
     */
    function (): void {
        expect(fn () => new DockerMachineTemplateManifest(
            metadata: new ManifestMetadata(name: 'x'),
        ))->toThrow(InvalidArgumentException::class);
    });

test('spec serializes with extra mounts',
    /**
     * @throws Throwable
     */
    function (): void {
        $spec = new DockerMachineTemplateSpec;

        expect($spec->toArray()['template']['spec']['extraMounts'])->toHaveCount(1)
            ->and($spec->toArray()['template']['spec']['extraMounts'][0]['containerPath'])->toBe('/var/run/docker.sock');
    });
