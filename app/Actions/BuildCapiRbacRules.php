<?php

declare(strict_types=1);

namespace App\Actions;

use App\Http\Integrations\Kubernetes\Data\RuleData;
use App\Http\Integrations\Kubernetes\Enums\RbacApiGroup;
use App\Http\Integrations\Kubernetes\Enums\RbacResource;
use App\Http\Integrations\Kubernetes\Enums\RbacVerb;

final class BuildCapiRbacRules
{
    /**
     * @return list<RuleData>
     */
    public function handle(): array
    {
        return [
            new RuleData(
                apiGroups: [
                    RbacApiGroup::CapiCore,
                    RbacApiGroup::CapiInfrastructure,
                    RbacApiGroup::CapiBootstrap,
                    RbacApiGroup::CapiControlPlane,
                ],
                resources: [RbacResource::All],
                verbs: [RbacVerb::All],
            ),
            new RuleData(
                apiGroups: [RbacApiGroup::Core],
                resources: [RbacResource::Secrets, RbacResource::ConfigMaps],
                verbs: [RbacVerb::All],
            ),
        ];
    }
}
