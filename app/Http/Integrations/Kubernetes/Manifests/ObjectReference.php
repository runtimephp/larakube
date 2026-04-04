<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Manifests;

use App\Http\Integrations\Kubernetes\Enums\ApiVersion;
use App\Http\Integrations\Kubernetes\Enums\Kind;

final readonly class ObjectReference
{
    public function __construct(
        public ApiVersion $apiVersion,
        public Kind $kind,
        public string $name,
    ) {}

    /**
     * @return array{apiVersion: string, kind: string, name: string}
     */
    public function toArray(): array
    {
        return [
            'apiVersion' => $this->apiVersion->value,
            'kind' => $this->kind->value,
            'name' => $this->name,
        ];
    }
}
