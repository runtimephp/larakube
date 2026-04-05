<?php

declare(strict_types=1);

use App\Http\Integrations\Kubernetes\Manifests\Capi\CidrBlock;
use App\Http\Integrations\Kubernetes\Manifests\Capi\ClusterNetworkSpec;

test('serializes with defaults',
    /**
     * @throws Throwable
     */
    function (): void {
        $spec = new ClusterNetworkSpec;

        expect($spec->toArray())->toBe([
            'pods' => ['cidrBlocks' => ['192.168.0.0/16']],
            'services' => ['cidrBlocks' => ['10.128.0.0/12']],
        ]);
    });

test('serializes with custom cidrs',
    /**
     * @throws Throwable
     */
    function (): void {
        $spec = new ClusterNetworkSpec(
            podCidrBlocks: [new CidrBlock('10.244.0.0/16')],
            serviceCidrBlocks: [new CidrBlock('10.96.0.0/12')],
        );

        expect($spec->toArray())->toBe([
            'pods' => ['cidrBlocks' => ['10.244.0.0/16']],
            'services' => ['cidrBlocks' => ['10.96.0.0/12']],
        ]);
    });
