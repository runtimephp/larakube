<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Manifests;

final readonly class ContainerSpec
{
    /**
     * @param  list<ContainerPort>  $ports
     * @param  list<EnvVar>  $env
     */
    public function __construct(
        public string $name,
        public string $image,
        public array $ports = [],
        public array $env = [],
    ) {}

    /**
     * @return array{name: string, image: string, ports?: list<array{containerPort: int, protocol: string, name?: string}>, env?: list<array{name: string, value: string}>}
     */
    public function toArray(): array
    {
        $container = [
            'name' => $this->name,
            'image' => $this->image,
        ];

        if ($this->ports !== []) {
            $container['ports'] = array_map(
                static fn (ContainerPort $port): array => $port->toArray(),
                $this->ports,
            );
        }

        if ($this->env !== []) {
            $container['env'] = array_map(
                static fn (EnvVar $envVar): array => $envVar->toArray(),
                $this->env,
            );
        }

        return $container;
    }
}
