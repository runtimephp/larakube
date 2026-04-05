<?php

declare(strict_types=1);

use App\Http\Integrations\Kubernetes\Enums\ApiVersion;
use App\Http\Integrations\Kubernetes\Enums\Kind;
use App\Http\Integrations\Kubernetes\Manifests\Capi\KubeadmConfigTemplateManifest;
use App\Http\Integrations\Kubernetes\Manifests\Capi\KubeadmConfigTemplateSpec;
use App\Http\Integrations\Kubernetes\Manifests\ManifestMetadata;

test('serializes and exposes routing metadata',
    /**
     * @throws Throwable
     */
    function (): void {
        $manifest = new KubeadmConfigTemplateManifest(
            metadata: new ManifestMetadata(name: 'my-md-0', namespace: 'kuven-org-123'),
        );

        expect($manifest->apiVersion())->toBe(ApiVersion::CapiBootstrapV1Beta1)
            ->and($manifest->kind())->toBe(Kind::KubeadmConfigTemplate)
            ->and($manifest->resource())->toBe('kubeadmconfigtemplates')
            ->and($manifest->namespace())->toBe('kuven-org-123')
            ->and($manifest->isClusterScoped())->toBeFalse()
            ->and($manifest->toArray()['spec'])->toBeArray();
    });

test('rejects missing namespace',
    /**
     * @throws Throwable
     */
    function (): void {
        expect(fn () => new KubeadmConfigTemplateManifest(
            metadata: new ManifestMetadata(name: 'x'),
        ))->toThrow(InvalidArgumentException::class);
    });

test('spec serializes with defaults',
    /**
     * @throws Throwable
     */
    function (): void {
        $spec = new KubeadmConfigTemplateSpec;

        expect($spec->toArray()['template']['spec']['joinConfiguration']['nodeRegistration']['kubeletExtraArgs'])->toBeArray();
    });
