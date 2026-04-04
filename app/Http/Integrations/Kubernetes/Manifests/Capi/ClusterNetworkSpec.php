<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Manifests\Capi;

final readonly class ClusterNetworkSpec
{
    /**
     * @param  list<CidrBlock>  $podCidrBlocks
     * @param  list<CidrBlock>  $serviceCidrBlocks
     */
    public function __construct(
        public array $podCidrBlocks = [new CidrBlock('192.168.0.0/16')],
        public array $serviceCidrBlocks = [new CidrBlock('10.128.0.0/12')],
    ) {}

    /**
     * @return array{pods: array{cidrBlocks: list<string>}, services: array{cidrBlocks: list<string>}}
     */
    public function toArray(): array
    {
        return [
            'pods' => [
                'cidrBlocks' => array_map(
                    fn (CidrBlock $cidr): string => $cidr->toString(),
                    $this->podCidrBlocks,
                ),
            ],
            'services' => [
                'cidrBlocks' => array_map(
                    fn (CidrBlock $cidr): string => $cidr->toString(),
                    $this->serviceCidrBlocks,
                ),
            ],
        ];
    }
}
