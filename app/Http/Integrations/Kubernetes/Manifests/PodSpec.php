<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Manifests;

final readonly class PodSpec
{
    /**
     * @param  list<ContainerSpec>  $containers
     */
    public function __construct(
        public array $containers,
        public ?string $serviceAccountName = null,
    ) {}

    /**
     * @return array{containers: list<array{name: string, image: string, ports?: list<array{containerPort: int, protocol: string, name?: string}>, env?: list<array{name: string, value: string}>}>, serviceAccountName?: string}
     */
    public function toArray(): array
    {
        $spec = [
            'containers' => array_map(
                static fn (ContainerSpec $container): array => $container->toArray(),
                $this->containers,
            ),
        ];

        if ($this->serviceAccountName !== null) {
            $spec['serviceAccountName'] = $this->serviceAccountName;
        }

        return $spec;
    }
}
