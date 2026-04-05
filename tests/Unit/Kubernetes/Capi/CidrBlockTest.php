<?php

declare(strict_types=1);

use App\Http\Integrations\Kubernetes\Manifests\Capi\CidrBlock;

test('converts to string',
    /**
     * @throws Throwable
     */
    function (): void {
        $cidr = new CidrBlock('10.0.0.0/8');

        expect($cidr->toString())->toBe('10.0.0.0/8');
    });
