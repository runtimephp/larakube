<?php

declare(strict_types=1);

namespace App\Services\InMemory;

use App\Contracts\FirewallService;
use App\Contracts\NatGatewayService;
use App\Contracts\NetworkService;
use App\Contracts\ServerService;
use App\Contracts\SshKeyService;
use App\Enums\CloudProviderType;
use App\Services\CloudProviderFactory;

/**
 * Test factory that returns InMemory services for all provider types.
 *
 * Use this in tests to avoid mocking CloudProviderFactory.
 */
final class InMemoryCloudProviderFactory extends CloudProviderFactory
{
    public function __construct(
        private readonly ?ServerService $serverService = null,
        private readonly ?SshKeyService $sshKeyService = null,
        private readonly ?NetworkService $networkService = null,
        private readonly ?FirewallService $firewallService = null,
        private readonly ?NatGatewayService $natGatewayService = null,
    ) {}

    public function makeServerService(CloudProviderType $type, ?string $token = null): ServerService
    {
        if ($this->serverService !== null) {
            return $this->serverService;
        }

        return parent::makeServerService($type, $token); // @codeCoverageIgnore
    }

    public function makeSshKeyService(CloudProviderType $type, ?string $token = null): SshKeyService
    {
        if ($this->sshKeyService !== null) {
            return $this->sshKeyService;
        }

        return parent::makeSshKeyService($type, $token); // @codeCoverageIgnore
    }

    public function makeNetworkService(CloudProviderType $type, ?string $token = null): NetworkService
    {
        if ($this->networkService !== null) {
            return $this->networkService;
        }

        return parent::makeNetworkService($type, $token); // @codeCoverageIgnore
    }

    public function makeFirewallService(CloudProviderType $type, ?string $token = null): FirewallService
    {
        if ($this->firewallService !== null) {
            return $this->firewallService;
        }

        return parent::makeFirewallService($type, $token); // @codeCoverageIgnore
    }

    public function makeNatGatewayService(CloudProviderType $type, ?string $token = null): NatGatewayService
    {
        if ($this->natGatewayService !== null) {
            return $this->natGatewayService;
        }

        return parent::makeNatGatewayService($type, $token); // @codeCoverageIgnore
    }
}
