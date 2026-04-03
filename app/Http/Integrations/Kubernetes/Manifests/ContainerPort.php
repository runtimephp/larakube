<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Manifests;

use App\Http\Integrations\Kubernetes\Enums\PortProtocol;

final readonly class ContainerPort
{
    public function __construct(
        public int $containerPort,
        public PortProtocol $protocol = PortProtocol::Tcp,
        public ?string $name = null,
    ) {}

    /**
     * @return array{containerPort: int, protocol: string, name?: string}
     */
    public function toArray(): array
    {
        $port = [
            'containerPort' => $this->containerPort,
            'protocol' => $this->protocol->value,
        ];

        if ($this->name !== null) {
            $port['name'] = $this->name;
        }

        return $port;
    }
}
