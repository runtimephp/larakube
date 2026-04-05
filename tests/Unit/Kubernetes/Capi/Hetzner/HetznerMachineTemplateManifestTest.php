<?php

declare(strict_types=1);

use App\Http\Integrations\Kubernetes\Enums\ApiVersion;
use App\Http\Integrations\Kubernetes\Enums\Kind;
use App\Http\Integrations\Kubernetes\Manifests\Capi\Hetzner\HetznerMachineTemplateManifest;
use App\Http\Integrations\Kubernetes\Manifests\Capi\Hetzner\HetznerMachineTemplateSpec;
use App\Http\Integrations\Kubernetes\Manifests\ManifestMetadata;

test('serializes and exposes routing metadata',
    /**
     * @throws Throwable
     */
    function (): void {
        $manifest = new HetznerMachineTemplateManifest(
            metadata: new ManifestMetadata(name: 'prod-cp', namespace: 'kuven-org-123'),
            spec: new HetznerMachineTemplateSpec(serverType: 'cx31'),
        );

        expect($manifest->apiVersion())->toBe(ApiVersion::CapiInfrastructureV1Beta1)
            ->and($manifest->kind())->toBe(Kind::HetznerMachineTemplate)
            ->and($manifest->resource())->toBe('hetznermachinetemplates')
            ->and($manifest->namespace())->toBe('kuven-org-123')
            ->and($manifest->isClusterScoped())->toBeFalse()
            ->and($manifest->toArray()['spec']['template']['spec']['serverType'])->toBe('cx31');
    });

test('rejects missing namespace',
    /**
     * @throws Throwable
     */
    function (): void {
        expect(fn () => new HetznerMachineTemplateManifest(
            metadata: new ManifestMetadata(name: 'x'),
            spec: new HetznerMachineTemplateSpec(serverType: 'cx31'),
        ))->toThrow(InvalidArgumentException::class);
    });

test('spec serializes with server type',
    /**
     * @throws Throwable
     */
    function (): void {
        $spec = new HetznerMachineTemplateSpec(serverType: 'cpx41');

        expect($spec->toArray())->toBe([
            'template' => [
                'spec' => [
                    'serverType' => 'cpx41',
                ],
            ],
        ]);
    });
