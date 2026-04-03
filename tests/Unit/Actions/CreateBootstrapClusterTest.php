<?php

declare(strict_types=1);

use App\Actions\CreateBootstrapCluster;
use App\Contracts\BootstrapClusterService;
use App\Services\InMemory\InMemoryBootstrapClusterService;

beforeEach(function (): void {
    $this->service = new InMemoryBootstrapClusterService;
    $this->app->instance(BootstrapClusterService::class, $this->service);
    $this->action = $this->app->make(CreateBootstrapCluster::class);
});

test('creates a bootstrap cluster',
    /**
     * @throws Throwable
     */
    function (): void {
        $this->action->handle('kuven-mgmt');

        expect($this->service->exists('kuven-mgmt'))->toBeTrue();
    });

test('skips creation if cluster already exists',
    /**
     * @throws Throwable
     */
    function (): void {
        $this->service->addCluster('kuven-mgmt');

        $this->action->handle('kuven-mgmt');

        expect($this->service->exists('kuven-mgmt'))->toBeTrue()
            ->and($this->service->createCount())->toBe(0);
    });

test('destroys a bootstrap cluster',
    /**
     * @throws Throwable
     */
    function (): void {
        $this->service->addCluster('kuven-mgmt');

        $this->service->destroy('kuven-mgmt');

        expect($this->service->exists('kuven-mgmt'))->toBeFalse();
    });
