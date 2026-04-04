<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ClusterManifestGenerator;
use App\Data\CreateClusterManifestData;
use App\Http\Integrations\Kubernetes\Enums\ApiVersion;
use App\Http\Integrations\Kubernetes\Enums\Kind;
use App\Http\Integrations\Kubernetes\Manifests\Capi\ClusterManifest;
use App\Http\Integrations\Kubernetes\Manifests\Capi\ClusterSpec;
use App\Http\Integrations\Kubernetes\Manifests\Capi\Docker\DockerClusterManifest;
use App\Http\Integrations\Kubernetes\Manifests\Capi\Docker\DockerMachineTemplateManifest;
use App\Http\Integrations\Kubernetes\Manifests\Capi\KubeadmConfigTemplateManifest;
use App\Http\Integrations\Kubernetes\Manifests\Capi\KubeadmControlPlaneManifest;
use App\Http\Integrations\Kubernetes\Manifests\Capi\KubeadmControlPlaneSpec;
use App\Http\Integrations\Kubernetes\Manifests\Capi\MachineDeploymentManifest;
use App\Http\Integrations\Kubernetes\Manifests\Capi\MachineDeploymentSpec;
use App\Http\Integrations\Kubernetes\Manifests\ManifestMetadata;
use App\Http\Integrations\Kubernetes\Manifests\ObjectReference;

final class DockerClusterManifestGenerator implements ClusterManifestGenerator
{
    public function generate(CreateClusterManifestData $createClusterManifestData): array
    {
        $name = $createClusterManifestData->name;
        $namespace = $createClusterManifestData->namespace;
        $version = $createClusterManifestData->kubernetesVersion;

        return [
            new ClusterManifest(
                metadata: new ManifestMetadata(name: $name, namespace: $namespace),
                spec: new ClusterSpec(
                    controlPlaneRef: new ObjectReference(ApiVersion::CapiControlPlaneV1Beta1, Kind::KubeadmControlPlane, "{$name}-control-plane"),
                    infrastructureRef: new ObjectReference(ApiVersion::CapiInfrastructureV1Beta1, Kind::DockerCluster, $name),
                ),
            ),
            new DockerClusterManifest(
                metadata: new ManifestMetadata(name: $name, namespace: $namespace),
            ),
            new KubeadmControlPlaneManifest(
                metadata: new ManifestMetadata(name: "{$name}-control-plane", namespace: $namespace),
                spec: new KubeadmControlPlaneSpec(
                    replicas: $createClusterManifestData->controlPlaneCount,
                    version: $version,
                    infrastructureRef: new ObjectReference(ApiVersion::CapiInfrastructureV1Beta1, Kind::DockerMachineTemplate, "{$name}-control-plane"),
                ),
            ),
            new DockerMachineTemplateManifest(
                metadata: new ManifestMetadata(name: "{$name}-control-plane", namespace: $namespace),
            ),
            new MachineDeploymentManifest(
                metadata: new ManifestMetadata(name: "{$name}-md-0", namespace: $namespace),
                spec: new MachineDeploymentSpec(
                    clusterName: $name,
                    replicas: $createClusterManifestData->workerCount,
                    version: $version,
                    bootstrapConfigRef: new ObjectReference(ApiVersion::CapiBootstrapV1Beta1, Kind::KubeadmConfigTemplate, "{$name}-md-0"),
                    infrastructureRef: new ObjectReference(ApiVersion::CapiInfrastructureV1Beta1, Kind::DockerMachineTemplate, "{$name}-md-0"),
                ),
            ),
            new DockerMachineTemplateManifest(
                metadata: new ManifestMetadata(name: "{$name}-md-0", namespace: $namespace),
            ),
            new KubeadmConfigTemplateManifest(
                metadata: new ManifestMetadata(name: "{$name}-md-0", namespace: $namespace),
            ),
        ];
    }
}
