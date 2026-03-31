<?php

declare(strict_types=1);

use App\Actions\StoreKubeconfig;
use App\Models\Infrastructure;
use App\Models\KubernetesCluster;
use Illuminate\Support\Facades\Storage;

test('stores kubeconfig and creates kubernetes cluster',
    /**
     * @throws Throwable
     */
    function (): void {
        Storage::fake('local');

        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->provisioning()->createQuietly();

        $kubeconfig = "apiVersion: v1\nclusters:\n- cluster:\n    server: https://10.0.1.1:6443\n";

        Storage::disk('local')->put("kubeconfigs/{$infrastructure->id}.conf", $kubeconfig);

        $action = new StoreKubeconfig();
        $action->handle($infrastructure);

        /** @var KubernetesCluster $cluster */
        $cluster = $infrastructure->kubernetesClusters()->first();

        expect($cluster)->not->toBeNull()
            ->and($cluster->name)->toBe("{$infrastructure->name}-cluster")
            ->and($cluster->version)->toBe('1.31')
            ->and($cluster->api_endpoint)->toBe('https://10.0.1.1:6443')
            ->and($cluster->pod_cidr)->toBe('10.244.0.0/16')
            ->and($cluster->service_cidr)->toBe('10.96.0.0/12');

        Storage::disk('local')->assertMissing("kubeconfigs/{$infrastructure->id}.conf");
    });

test('throws when kubeconfig file is missing',
    /**
     * @throws Throwable
     */
    function (): void {
        Storage::fake('local');

        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->provisioning()->createQuietly();

        $action = new StoreKubeconfig();
        $action->handle($infrastructure);
    })->throws(RuntimeException::class, 'Kubeconfig not found');

test('updates existing kubernetes cluster',
    /**
     * @throws Throwable
     */
    function (): void {
        Storage::fake('local');

        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->provisioning()->createQuietly();

        KubernetesCluster::factory()->createQuietly([
            'infrastructure_id' => $infrastructure->id,
            'name' => 'old-cluster',
            'version' => '1.30',
        ]);

        $kubeconfig = "apiVersion: v1\nserver: https://10.0.1.1:6443\n";

        Storage::disk('local')->put("kubeconfigs/{$infrastructure->id}.conf", $kubeconfig);

        $action = new StoreKubeconfig();
        $action->handle($infrastructure);

        expect($infrastructure->kubernetesClusters()->count())->toBe(1);

        /** @var KubernetesCluster $cluster */
        $cluster = $infrastructure->kubernetesClusters()->first();

        expect($cluster->api_endpoint)->toBe('https://10.0.1.1:6443');
    });

test('extracts null api endpoint when no server found in kubeconfig',
    /**
     * @throws Throwable
     */
    function (): void {
        Storage::fake('local');

        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->provisioning()->createQuietly();

        $kubeconfig = "apiVersion: v1\nclusters:\n- cluster:\n    certificate-authority-data: abc123\n";

        Storage::disk('local')->put("kubeconfigs/{$infrastructure->id}.conf", $kubeconfig);

        $action = new StoreKubeconfig();
        $action->handle($infrastructure);

        /** @var KubernetesCluster $cluster */
        $cluster = $infrastructure->kubernetesClusters()->first();

        expect($cluster->api_endpoint)->toBeNull();
    });
