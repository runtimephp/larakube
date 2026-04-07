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
            spec: new HetznerClusterSpec(controlPlaneRegions: ['nuremberg'], hetznerSecretName: 'prod-secret'),
        );

        expect($manifest->apiVersion())->toBe(ApiVersion::CapiInfrastructureV1Beta1)
            ->and($manifest->kind())->toBe(Kind::HetznerCluster)
            ->and($manifest->resource())->toBe('hetznerclusters')
            ->and($manifest->namespace())->toBe('kuven-org-123')
            ->and($manifest->isClusterScoped())->toBeFalse()
            ->and($manifest->toArray()['spec']['controlPlaneRegions'])->toBe(['nuremberg'])
            ->and($manifest->toArray()['spec']['hetznerSecretRef']['name'])->toBe('prod-secret');
    });

test('rejects missing namespace',
    /**
     * @throws Throwable
     */
    function (): void {
        expect(fn () => new HetznerClusterManifest(
            metadata: new ManifestMetadata(name: 'x'),
            spec: new HetznerClusterSpec(controlPlaneRegions: ['nbg1'], hetznerSecretName: 'x'),
        ))->toThrow(InvalidArgumentException::class);
    });

test('spec serializes with custom ssh key',
    /**
     * @throws Throwable
     */
    function (): void {
        $spec = new HetznerClusterSpec(
            controlPlaneRegions: ['falkenstein'],
            hetznerSecretName: 'my-secret',
            sshKeyName: 'my-key',
        );

        expect($spec->toArray()['sshKeys']['hcloud'])->toBe([['name' => 'my-key']]);
    });

test('spec rejects empty controlPlaneRegions',
    /**
     * @throws Throwable
     */
    function (): void {
        expect(fn () => new HetznerClusterSpec(controlPlaneRegions: [], hetznerSecretName: 'x'))
            ->toThrow(InvalidArgumentException::class);
    });

test('spec rejects empty hetznerSecretName',
    /**
     * @throws Throwable
     */
    function (): void {
        expect(fn () => new HetznerClusterSpec(controlPlaneRegions: ['nbg1'], hetznerSecretName: ''))
            ->toThrow(InvalidArgumentException::class);
    });

test('spec omits sshKeys names when sshKeyName is null',
    /**
     * @throws Throwable
     */
    function (): void {
        $spec = new HetznerClusterSpec(controlPlaneRegions: ['nbg1'], hetznerSecretName: 'x');

        expect($spec->toArray()['sshKeys']['hcloud'])->toBe([]);
    });

test('spec includes hetznerSecretRef with robot keys',
    /**
     * @throws Throwable
     */
    function (): void {
        $spec = new HetznerClusterSpec(controlPlaneRegions: ['nbg1'], hetznerSecretName: 'my-secret');

        expect($spec->toArray()['hetznerSecretRef'])->toBe([
            'key' => [
                'hcloudToken' => 'hcloud',
                'hetznerRobotUser' => 'robot-user',
                'hetznerRobotPassword' => 'robot-password',
            ],
            'name' => 'my-secret',
        ]);
    });

test('spec includes controlPlaneLoadBalancer with region',
    /**
     * @throws Throwable
     */
    function (): void {
        $spec = new HetznerClusterSpec(controlPlaneRegions: ['fsn1'], hetznerSecretName: 'x');

        expect($spec->toArray()['controlPlaneLoadBalancer'])->toBe([
            'region' => 'fsn1',
        ]);
    });
