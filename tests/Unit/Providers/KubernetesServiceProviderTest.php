<?php

declare(strict_types=1);

use App\Http\Integrations\Kubernetes\KubernetesConnector;
use App\Models\ManagementCluster;

test('resolves connector from ready management cluster',
    /**
     * @throws Throwable
     */
    function (): void {
        ManagementCluster::factory()->ready()->create([
            'kubeconfig' => "apiVersion: v1\nclusters:\n- cluster:\n    server: https://127.0.0.1:6443\nusers:\n- user:\n    token: test-token-123",
        ]);

        // Clear singleton so it re-resolves
        $this->app->forgetInstance(KubernetesConnector::class);

        $connector = $this->app->make(KubernetesConnector::class);

        expect($connector)->toBeInstanceOf(KubernetesConnector::class)
            ->and($connector->resolveBaseUrl())->toBe('https://127.0.0.1:6443');
    });

test('resolves empty connector when no management cluster exists',
    /**
     * @throws Throwable
     */
    function (): void {
        $this->app->forgetInstance(KubernetesConnector::class);

        $connector = $this->app->make(KubernetesConnector::class);

        expect($connector)->toBeInstanceOf(KubernetesConnector::class)
            ->and($connector->resolveBaseUrl())->toBe('');
    });
