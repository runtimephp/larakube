<?php

declare(strict_types=1);

use App\Http\Integrations\Kubernetes\Enums\ApiVersion;
use App\Http\Integrations\Kubernetes\Enums\Kind;
use App\Http\Integrations\Kubernetes\Manifests\Capi\Hetzner\HCloudMachineTemplateManifest;
use App\Http\Integrations\Kubernetes\Manifests\Capi\Hetzner\HCloudMachineTemplateSpec;
use App\Http\Integrations\Kubernetes\Manifests\ManifestMetadata;

test('serializes and exposes routing metadata',
    /**
     * @throws Throwable
     */
    function (): void {
        $manifest = new HCloudMachineTemplateManifest(
            metadata: new ManifestMetadata(name: 'cp-template', namespace: 'kuven-org-123'),
            spec: new HCloudMachineTemplateSpec(type: 'cpx22'),
        );

        expect($manifest->apiVersion())->toBe(ApiVersion::CapiInfrastructureV1Beta1)
            ->and($manifest->kind())->toBe(Kind::HCloudMachineTemplate)
            ->and($manifest->resource())->toBe('hcloudmachinetemplates')
            ->and($manifest->namespace())->toBe('kuven-org-123')
            ->and($manifest->isClusterScoped())->toBeFalse()
            ->and($manifest->toArray()['spec']['template']['spec']['type'])->toBe('cpx22')
            ->and($manifest->toArray()['spec']['template']['spec']['imageName'])->toBe('ubuntu-24.04');
    });

test('rejects missing namespace',
    /**
     * @throws Throwable
     */
    function (): void {
        expect(fn () => new HCloudMachineTemplateManifest(
            metadata: new ManifestMetadata(name: 'x'),
            spec: new HCloudMachineTemplateSpec(type: 'cpx22'),
        ))->toThrow(InvalidArgumentException::class);
    });
