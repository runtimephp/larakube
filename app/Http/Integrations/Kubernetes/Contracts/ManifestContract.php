<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Contracts;

use App\Http\Integrations\Kubernetes\Enums\ApiVersion;
use App\Http\Integrations\Kubernetes\Enums\Kind;

interface ManifestContract
{
    public function apiVersion(): ApiVersion;

    public function kind(): Kind;

    public function resource(): string;

    public function namespace(): ?string;

    public function isClusterScoped(): bool;

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
