<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ClusterManifestGenerator;
use App\Data\CreateClusterManifestData;
use App\Http\Integrations\Kubernetes\Enums\ApiVersion;
use App\Http\Integrations\Kubernetes\Enums\Kind;
use App\Http\Integrations\Kubernetes\Enums\SecretType;
use App\Http\Integrations\Kubernetes\Manifests\Capi\ClusterManifest;
use App\Http\Integrations\Kubernetes\Manifests\Capi\ClusterSpec;
use App\Http\Integrations\Kubernetes\Manifests\Capi\Hetzner\HetznerClusterManifest;
use App\Http\Integrations\Kubernetes\Manifests\Capi\Hetzner\HetznerClusterSpec;
use App\Http\Integrations\Kubernetes\Manifests\Capi\Hetzner\HetznerMachineTemplateManifest;
use App\Http\Integrations\Kubernetes\Manifests\Capi\Hetzner\HetznerMachineTemplateSpec;
use App\Http\Integrations\Kubernetes\Manifests\Capi\KubeadmConfigTemplateManifest;
use App\Http\Integrations\Kubernetes\Manifests\Capi\KubeadmControlPlaneManifest;
use App\Http\Integrations\Kubernetes\Manifests\Capi\KubeadmControlPlaneSpec;
use App\Http\Integrations\Kubernetes\Manifests\Capi\MachineDeploymentManifest;
use App\Http\Integrations\Kubernetes\Manifests\Capi\MachineDeploymentSpec;
use App\Http\Integrations\Kubernetes\Manifests\ManifestMetadata;
use App\Http\Integrations\Kubernetes\Manifests\ObjectReference;
use App\Http\Integrations\Kubernetes\Manifests\SecretManifest;
use App\Http\Integrations\Kubernetes\Manifests\SecretStringData;

final class HetznerClusterManifestGenerator implements ClusterManifestGenerator
{
    public function generate(CreateClusterManifestData $createClusterManifestData): array
    {
        $name = $createClusterManifestData->name;
        $namespace = $createClusterManifestData->namespace;
        $version = $createClusterManifestData->kubernetesVersion;
        $region = $createClusterManifestData->region ?? 'nbg1';
        $machineType = $createClusterManifestData->machineType ?? 'cx31';

        return [
            new SecretManifest(
                metadata: new ManifestMetadata(name: "{$name}-hetzner-credentials", namespace: $namespace),
                data: new SecretStringData(['hcloud' => '']),
                type: SecretType::Opaque,
            ),
            new ClusterManifest(
                metadata: new ManifestMetadata(name: $name, namespace: $namespace),
                spec: new ClusterSpec(
                    controlPlaneRef: new ObjectReference(ApiVersion::CapiControlPlaneV1Beta1, Kind::KubeadmControlPlane, "{$name}-control-plane"),
                    infrastructureRef: new ObjectReference(ApiVersion::CapiInfrastructureV1Beta1, Kind::HetznerCluster, $name),
                ),
            ),
            new HetznerClusterManifest(
                metadata: new ManifestMetadata(name: $name, namespace: $namespace),
                spec: new HetznerClusterSpec(controlPlaneRegion: $region, sshKeyName: $name),
            ),
            new KubeadmControlPlaneManifest(
                metadata: new ManifestMetadata(name: "{$name}-control-plane", namespace: $namespace),
                spec: new KubeadmControlPlaneSpec(
                    replicas: $createClusterManifestData->controlPlaneCount,
                    version: $version,
                    infrastructureRef: new ObjectReference(ApiVersion::CapiInfrastructureV1Beta1, Kind::HetznerMachineTemplate, "{$name}-control-plane"),
                ),
            ),
            new HetznerMachineTemplateManifest(
                metadata: new ManifestMetadata(name: "{$name}-control-plane", namespace: $namespace),
                spec: new HetznerMachineTemplateSpec(serverType: $machineType),
            ),
            new MachineDeploymentManifest(
                metadata: new ManifestMetadata(name: "{$name}-md-0", namespace: $namespace),
                spec: new MachineDeploymentSpec(
                    clusterName: $name,
                    replicas: $createClusterManifestData->workerCount,
                    version: $version,
                    bootstrapConfigRef: new ObjectReference(ApiVersion::CapiBootstrapV1Beta1, Kind::KubeadmConfigTemplate, "{$name}-md-0"),
                    infrastructureRef: new ObjectReference(ApiVersion::CapiInfrastructureV1Beta1, Kind::HetznerMachineTemplate, "{$name}-md-0"),
                ),
            ),
            new HetznerMachineTemplateManifest(
                metadata: new ManifestMetadata(name: "{$name}-md-0", namespace: $namespace),
                spec: new HetznerMachineTemplateSpec(serverType: $machineType),
            ),
            new KubeadmConfigTemplateManifest(
                metadata: new ManifestMetadata(name: "{$name}-md-0", namespace: $namespace),
            ),
        ];
    }
}
