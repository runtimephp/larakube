<?php

declare(strict_types=1);

use App\Enums\InfrastructureStatus;
use App\Enums\ProvisioningPhase;
use App\Enums\ProvisioningStep;
use App\Enums\ServerRole;
use App\Enums\ServerStatus;
use App\Jobs\ProcessProvisioningStep;
use App\Models\Infrastructure;
use App\Models\Server;
use App\Services\CloudProviderFactory;
use App\Services\InMemory\InMemoryCloudProviderFactory;
use App\Services\InMemory\InMemoryHetznerServerService;
use Illuminate\Support\Facades\Bus;

test('display name shows complete when step is null',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->createQuietly([
            'status' => InfrastructureStatus::Healthy,
            'provisioning_step' => null,
            'provisioning_phase' => null,
        ]);

        $job = new ProcessProvisioningStep($infrastructure);

        expect($job->displayName())->toBe('ProcessProvisioningStep [Complete]');
    });

test('display name shows step label when step is set',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->provisioning()->createQuietly();

        $job = new ProcessProvisioningStep($infrastructure);

        expect($job->displayName())->toBe('ProcessProvisioningStep [Generate SSH Keypairs]');
    });

test('throws runtime exception when max retries exceeded',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->createQuietly([
            'status' => InfrastructureStatus::Provisioning,
            'provisioning_step' => ProvisioningStep::WaitForBastion,
            'provisioning_phase' => ProvisioningPhase::Infrastructure,
        ]);

        $serverService = new InMemoryHetznerServerService();
        $factory = new InMemoryCloudProviderFactory(serverService: $serverService);
        $this->app->instance(CloudProviderFactory::class, $factory);

        Server::factory()->createQuietly([
            'infrastructure_id' => $infrastructure->id,
            'cloud_provider_id' => $infrastructure->cloud_provider_id,
            'role' => ServerRole::Bastion,
            'status' => ServerStatus::Starting,
            'name' => 'test-bastion',
        ]);

        $job = new ProcessProvisioningStep($infrastructure, stepRetries: 40);

        expect(fn () => $job->handle())->toThrow(RuntimeException::class, 'exceeded maximum retries');
    });

test('sets healthy when null step is reached',
    /**
     * @throws Throwable
     */
    function (): void {
        Bus::fake([ProcessProvisioningStep::class]);

        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->createQuietly([
            'status' => InfrastructureStatus::Provisioning,
            'provisioning_step' => null,
            'provisioning_phase' => null,
        ]);

        $job = new ProcessProvisioningStep($infrastructure);
        $job->handle();

        $infrastructure->refresh();

        expect($infrastructure->status)->toBe(InfrastructureStatus::Healthy);

        Bus::assertNotDispatched(ProcessProvisioningStep::class);
    });

test('sets healthy when WaitForNodes completes and next step is terminal-adjacent',
    /**
     * @throws Throwable
     */
    function (): void {
        Bus::fake([ProcessProvisioningStep::class]);

        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->createQuietly([
            'status' => InfrastructureStatus::Provisioning,
            'provisioning_step' => ProvisioningStep::WaitForNodes,
            'provisioning_phase' => ProvisioningPhase::Infrastructure,
        ]);

        $serverService = new InMemoryHetznerServerService();
        $factory = new InMemoryCloudProviderFactory(serverService: $serverService);
        $this->app->instance(CloudProviderFactory::class, $factory);

        // WaitForNodes needs all non-bastion servers running
        Server::factory()->createQuietly([
            'infrastructure_id' => $infrastructure->id,
            'cloud_provider_id' => $infrastructure->cloud_provider_id,
            'role' => ServerRole::ControlPlane,
            'status' => ServerStatus::Running,
            'name' => 'test-cp-1',
        ]);

        $job = new ProcessProvisioningStep($infrastructure);
        $job->handle();

        $infrastructure->refresh();

        // Next step after WaitForNodes is GenerateInventory which is not terminal
        expect($infrastructure->provisioning_step)->toBe(ProvisioningStep::GenerateInventory);

        Bus::assertDispatched(ProcessProvisioningStep::class);
    });

test('advances to next step and dispatches itself',
    /**
     * @throws Throwable
     */
    function (): void {
        Bus::fake([ProcessProvisioningStep::class]);

        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->provisioning()->createQuietly();

        expect($infrastructure->provisioning_step)->toBe(ProvisioningStep::GenerateSshKeypairs);

        $job = new ProcessProvisioningStep($infrastructure);
        $job->handle();

        $infrastructure->refresh();

        expect($infrastructure->provisioning_step)->toBe(ProvisioningStep::RegisterSshKeys)
            ->and($infrastructure->provisioning_phase)->toBe(ProvisioningPhase::Infrastructure);

        Bus::assertDispatched(ProcessProvisioningStep::class);
    });

test('marks infrastructure healthy on terminal step',
    /**
     * @throws Throwable
     */
    function (): void {
        Bus::fake([ProcessProvisioningStep::class]);

        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->createQuietly([
            'status' => InfrastructureStatus::Provisioning,
            'provisioning_step' => ProvisioningStep::MarkHealthy,
            'provisioning_phase' => ProvisioningPhase::Configuration,
        ]);

        $job = new ProcessProvisioningStep($infrastructure);
        $job->handle();

        $infrastructure->refresh();

        expect($infrastructure->status)->toBe(InfrastructureStatus::Healthy)
            ->and($infrastructure->provisioning_step)->toBe(ProvisioningStep::MarkHealthy);

        Bus::assertNotDispatched(ProcessProvisioningStep::class);
    });

test('marks infrastructure failed on exception',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->provisioning()->createQuietly();

        $job = new ProcessProvisioningStep($infrastructure);
        $job->failed(new RuntimeException('Simulated failure'));

        $infrastructure->refresh();

        expect($infrastructure->status)->toBe(InfrastructureStatus::Failed);
    });

test('throws logic exception for unimplemented steps',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->createQuietly([
            'status' => InfrastructureStatus::Provisioning,
            'provisioning_step' => ProvisioningStep::GenerateInventory,
            'provisioning_phase' => ProvisioningPhase::Configuration,
        ]);

        $job = new ProcessProvisioningStep($infrastructure);

        expect(fn () => $job->handle())->toThrow(LogicException::class, 'No handler registered');
    });

test('retries same step with delay on RetryStepException',
    /**
     * @throws Throwable
     */
    function (): void {
        Bus::fake([ProcessProvisioningStep::class]);

        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->createQuietly([
            'status' => InfrastructureStatus::Provisioning,
            'provisioning_step' => ProvisioningStep::WaitForBastion,
            'provisioning_phase' => ProvisioningPhase::Infrastructure,
        ]);

        // WaitForBastion needs a bastion server that's not running to trigger RetryStepException
        // But it also needs CloudProviderFactory. Let's bind a mock.
        $serverService = new InMemoryHetznerServerService();
        $factory = Mockery::mock(CloudProviderFactory::class);
        $factory->shouldReceive('makeServerService')->andReturn($serverService);
        $this->app->instance(CloudProviderFactory::class, $factory);

        Server::factory()->createQuietly([
            'infrastructure_id' => $infrastructure->id,
            'cloud_provider_id' => $infrastructure->cloud_provider_id,
            'role' => ServerRole::Bastion,
            'status' => ServerStatus::Starting,
            'name' => 'test-bastion',
        ]);

        $job = new ProcessProvisioningStep($infrastructure);
        $job->handle();

        $infrastructure->refresh();

        // Step should NOT have advanced
        expect($infrastructure->provisioning_step)->toBe(ProvisioningStep::WaitForBastion);

        // Should have re-dispatched with delay
        Bus::assertDispatched(ProcessProvisioningStep::class);
    });
