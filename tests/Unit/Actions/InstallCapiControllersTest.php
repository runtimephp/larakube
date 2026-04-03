<?php

declare(strict_types=1);

use App\Actions\InstallCapiControllers;
use App\Contracts\CapiInstallerService;
use App\Services\InMemory\InMemoryCapiInstallerService;

beforeEach(function (): void {
    $this->service = new InMemoryCapiInstallerService;
    $this->app->instance(CapiInstallerService::class, $this->service);
    $this->action = $this->app->make(InstallCapiControllers::class);
});

test('installs capi controllers for a provider',
    /**
     * @throws Throwable
     */
    function (): void {
        $this->action->handle('docker', '/tmp/kubeconfig');

        expect($this->service->installations())->toHaveCount(1)
            ->and($this->service->installations()[0]['provider'])->toBe('docker')
            ->and($this->service->installations()[0]['kubeconfig'])->toBe('/tmp/kubeconfig');
    });

test('throws when capi installation fails',
    /**
     * @throws Throwable
     */
    function (): void {
        $this->service->shouldFail();

        expect(fn () => $this->action->handle('docker', '/tmp/kubeconfig'))
            ->toThrow(RuntimeException::class, 'Simulated CAPI installation failure');
    });
