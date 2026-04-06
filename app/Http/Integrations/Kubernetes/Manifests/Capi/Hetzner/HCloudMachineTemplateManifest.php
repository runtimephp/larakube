<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Manifests\Capi\Hetzner;

use App\Http\Integrations\Kubernetes\Contracts\ManifestContract;
use App\Http\Integrations\Kubernetes\Enums\ApiVersion;
use App\Http\Integrations\Kubernetes\Enums\Kind;
use App\Http\Integrations\Kubernetes\Manifests\ManifestMetadata;
use InvalidArgumentException;

final readonly class HCloudMachineTemplateManifest implements ManifestContract
{
    public function __construct(
        public ManifestMetadata $metadata,
        public HCloudMachineTemplateSpec $spec,
    ) {
        if ($this->metadata->namespace === null || mb_trim($this->metadata->namespace) === '') {
            throw new InvalidArgumentException('HCloudMachineTemplate manifests require a namespace.');
        }
    }

    public function apiVersion(): ApiVersion
    {
        return ApiVersion::CapiInfrastructureV1Beta1;
    }

    public function kind(): Kind
    {
        return Kind::HCloudMachineTemplate;
    }

    public function resource(): string
    {
        return 'hcloudmachinetemplates';
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
