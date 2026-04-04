<?php

declare(strict_types=1);

use App\Actions\BuildCapiRbacRules;
use App\Http\Integrations\Kubernetes\Data\RuleData;
use App\Http\Integrations\Kubernetes\Enums\RbacApiGroup;
use App\Http\Integrations\Kubernetes\Enums\RbacResource;
use App\Http\Integrations\Kubernetes\Enums\RbacVerb;

test('builds capi rbac rules with all api groups',
    /**
     * @throws Throwable
     */
    function (): void {
        $rules = app(BuildCapiRbacRules::class)->handle();

        expect($rules)->toHaveCount(2)
            ->and($rules[0])->toBeInstanceOf(RuleData::class)
            ->and($rules[0]->apiGroups)->toBe([
                RbacApiGroup::CapiCore,
                RbacApiGroup::CapiInfrastructure,
                RbacApiGroup::CapiBootstrap,
                RbacApiGroup::CapiControlPlane,
            ])
            ->and($rules[0]->resources)->toBe([RbacResource::All])
            ->and($rules[0]->verbs)->toBe([RbacVerb::All])
            ->and($rules[1]->apiGroups)->toBe([RbacApiGroup::Core])
            ->and($rules[1]->resources)->toBe([RbacResource::Secrets, RbacResource::ConfigMaps])
            ->and($rules[1]->verbs)->toBe([RbacVerb::All]);
    });
