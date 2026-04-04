<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Manifests\Capi\Docker;

use App\Http\Integrations\Kubernetes\Contracts\ManifestContract;
use App\Http\Integrations\Kubernetes\Enums\ApiVersion;
use App\Http\Integrations\Kubernetes\Enums\Kind;
use App\Http\Integrations\Kubernetes\Manifests\ManifestMetadata;
use InvalidArgumentException;

final readonly class DockerMachineTemplateManifest implements ManifestContract
{
    public function __construct(
        public ManifestMetadata $metadata,
        public DockerMachineTemplateSpec $spec = new DockerMachineTemplateSpec,
    ) {
        if ($this->metadata->namespace === null || mb_trim($this->metadata->namespace) === '') {
            throw new InvalidArgumentException('DockerMachineTemplate manifests require a namespace.');
        }
    }

    public function apiVersion(): ApiVersion
    {
        return ApiVersion::CapiInfrastructureV1Beta1;
    }

    public function kind(): Kind
    {
        return Kind::DockerMachineTemplate;
    }

    public function resource(): string
    {
        return 'dockermachinetemplates';
    }

    public function namespace(): string
    {
        return $this->metadata->namespace;
    }

    public function isClusterScoped(): bool
    {
        return false;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'apiVersion' => $this->apiVersion()->value,
            'kind' => $this->kind()->value,
            'metadata' => $this->metadata->toArray(),
            'spec' => $this->spec->toArray(),
        ];
    }
}
