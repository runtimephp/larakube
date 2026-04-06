<?php

declare(strict_types=1);

namespace App\Actions;

use App\Contracts\BootstrapClusterService;
use App\Contracts\CapiInstallerService;
use App\Contracts\KubeconfigReaderService;
use App\Contracts\ManagementClusterClient;
use App\Data\CreateManagementClusterData;
use App\Data\ManagementClusterData;
use App\Data\ProvisionManagementClusterData;
use Illuminate\Support\Facades\Process;
use RuntimeException;

final readonly class ProvisionManagementCluster
{
    private const CLUSTERCTL_VERSION = 'v1.8.10';

    private const CLUSTERCTL_BINARY_URL = 'https://github.com/kubernetes-sigs/cluster-api/releases/download/%s/clusterctl-%s-%s';

    public function __construct(
        private ManagementClusterClient $managementClusterClient,
        private ValidatePrerequisites $validatePrerequisites,
        private BootstrapClusterService $bootstrapClusterService,
        private CapiInstallerService $capiInstallerService,
        private KubeconfigReaderService $kubeconfigReaderService,
        private WriteKubeconfigToTempFile $writeKubeconfigToTempFile,
        private CleanupTempKubeconfig $cleanupTempKubeconfig,
    ) {}

    public function handle(ProvisionManagementClusterData $data): ManagementClusterData
    {
        $existing = $this->managementClusterClient->findByProviderAndRegion($data->provider, $data->region);

        if ($existing && ! $data->force) {
            throw new RuntimeException("Management cluster for provider [{$data->provider}] in region [{$data->region}] already exists.");
        }

        if ($existing) {
            $this->bootstrapClusterService->destroy($existing->name);
            $this->managementClusterClient->delete($existing->id);
        }

        $result = $this->validatePrerequisites->handle();

        if (! $result->ok) {
            throw new RuntimeException('Missing prerequisites: '.implode(', ', $result->missing));
        }

        $clusterName = "kuven-mgmt-{$data->region}";

        $cluster = $this->managementClusterClient->create(new CreateManagementClusterData(
            name: $clusterName,
            provider: $data->provider,
            region: $data->region,
            kubernetesVersion: $data->kubernetesVersion,
        ));

        $this->bootstrapClusterService->create($clusterName);

        $kubeconfigPath = $this->writeKubeconfigToTempFile->handle($clusterName);

        $this->capiInstallerService->init($data->provider, $kubeconfigPath);

        // Refresh kubeconfig — Kind API server port may change during init
        $kubeconfigPath = $this->writeKubeconfigToTempFile->handle($clusterName);

        if ($data->provider === 'hetzner') {
            $this->provisionHetznerManagementCluster($cluster->id, $clusterName, $kubeconfigPath, $data);
        }

        $this->cleanupTempKubeconfig->handle($kubeconfigPath);

        $kubeconfig = $this->kubeconfigReaderService->read($clusterName);
        $this->managementClusterClient->storeKubeconfig($cluster->id, $kubeconfig);
        $this->managementClusterClient->markReady($cluster->id);

        return new ManagementClusterData(
            id: $cluster->id,
            name: $cluster->name,
            provider: $cluster->provider,
            region: $cluster->region,
            status: 'ready',
            kubernetesVersion: $data->kubernetesVersion,
        );
    }

    private function provisionHetznerManagementCluster(
        string $clusterId,
        string $clusterName,
        string $kubeconfigPath,
        ProvisionManagementClusterData $data,
    ): void {
        $token = config('services.hetzner.token');

        if (! is_string($token) || $token === '') {
            throw new RuntimeException('HCLOUD_TOKEN environment variable is required for Hetzner provider.');
        }

        $this->waitForControllersReady($kubeconfigPath);

        $this->createHcloudSecret($token, $kubeconfigPath);

        $sshPrivateKey = $this->ensureSshKey($token, $clusterName);

        if ($sshPrivateKey !== null) {
            $this->managementClusterClient->storeSshPrivateKey($clusterId, $sshPrivateKey);
        }

        $clusterctlPath = $this->ensureClusterctl();

        $this->generateAndApplyCluster($clusterName, $data, $kubeconfigPath, $clusterctlPath);

        $this->waitForClusterReady($clusterName, $kubeconfigPath);

        $targetKubeconfigPath = $this->getWorkloadKubeconfig($clusterName, $kubeconfigPath, $clusterctlPath);

        $this->capiInstallerService->init($data->provider, $targetKubeconfigPath);

        $this->pivotToHetznerCluster($kubeconfigPath, $targetKubeconfigPath, $clusterctlPath);

        $this->bootstrapClusterService->destroy($clusterName);

        $this->cleanupTempKubeconfig->handle($targetKubeconfigPath);
    }

    private function waitForControllersReady(string $kubeconfigPath): void
    {
        $namespaces = ['capi-system', 'caph-system', 'capi-kubeadm-bootstrap-system', 'capi-kubeadm-control-plane-system'];

        foreach ($namespaces as $namespace) {
            $result = Process::timeout(120)->run(
                "kubectl wait deployment --all --for=condition=Available --timeout=120s --namespace={$namespace} --kubeconfig {$kubeconfigPath}"
            );

            if ($result->failed()) {
                throw new RuntimeException("CAPI controllers in {$namespace} did not become ready: {$result->errorOutput()}");
            }
        }

        foreach ($namespaces as $namespace) {
            Process::timeout(120)->run(
                "kubectl wait pod --all --for=condition=Ready --timeout=120s --namespace={$namespace} --kubeconfig {$kubeconfigPath}"
            );
        }

        sleep(5);
    }

    private function createHcloudSecret(string $token, string $kubeconfigPath): void
    {
        $result = Process::timeout(30)->run(
            "kubectl create secret generic hetzner --from-literal=hcloud={$token} --kubeconfig {$kubeconfigPath}"
        );

        if ($result->failed()) {
            throw new RuntimeException("Failed to create hcloud secret: {$result->errorOutput()}");
        }

        Process::timeout(30)->run(
            "kubectl patch secret hetzner -p '{\"metadata\":{\"labels\":{\"clusterctl.cluster.x-k8s.io/move\":\"\"}}}' --kubeconfig {$kubeconfigPath}"
        );
    }

    private function ensureSshKey(string $token, string $clusterName): ?string
    {
        $check = Process::timeout(30)->run(
            "curl -sf -H 'Authorization: Bearer {$token}' 'https://api.hetzner.cloud/v1/ssh_keys?name={$clusterName}'"
        );

        if ($check->successful()) {
            $response = json_decode($check->output(), true);
            if (! empty($response['ssh_keys'])) {
                return null;
            }
        }

        $keyPath = sys_get_temp_dir()."/{$clusterName}-ssh-key";

        Process::timeout(10)->run("ssh-keygen -t ed25519 -f {$keyPath} -N '' -C '{$clusterName}@kuven'");

        $publicKey = mb_trim((string) file_get_contents("{$keyPath}.pub"));
        $payload = json_encode(['name' => $clusterName, 'public_key' => $publicKey]);

        $result = Process::timeout(30)->run(
            "curl -sf -X POST -H 'Authorization: Bearer {$token}' -H 'Content-Type: application/json' -d '{$payload}' 'https://api.hetzner.cloud/v1/ssh_keys'"
        );

        if ($result->failed()) {
            throw new RuntimeException("Failed to create SSH key in Hetzner: {$result->errorOutput()}");
        }

        $privateKey = (string) file_get_contents($keyPath);

        @unlink($keyPath);
        @unlink("{$keyPath}.pub");

        return $privateKey;
    }

    private function ensureClusterctl(): string
    {
        $version = self::CLUSTERCTL_VERSION;
        $path = sys_get_temp_dir()."/clusterctl-{$version}";

        if (file_exists($path)) {
            return $path;
        }

        $os = PHP_OS_FAMILY === 'Darwin' ? 'darwin' : 'linux';
        $arch = php_uname('m') === 'arm64' ? 'arm64' : 'amd64';
        $url = sprintf(self::CLUSTERCTL_BINARY_URL, $version, $os, $arch);

        $result = Process::timeout(60)->run("curl -sL '{$url}' -o {$path} && chmod +x {$path}");

        if ($result->failed()) {
            throw new RuntimeException("Failed to download clusterctl {$version}: {$result->errorOutput()}");
        }

        return $path;
    }

    private function generateAndApplyCluster(
        string $clusterName,
        ProvisionManagementClusterData $data,
        string $kubeconfigPath,
        string $clusterctlPath,
    ): void {
        $env = [
            'HCLOUD_REGION' => $data->region,
            'HCLOUD_CONTROL_PLANE_MACHINE_TYPE' => 'cx23',
            'HCLOUD_WORKER_MACHINE_TYPE' => 'cx23',
            'KUBERNETES_VERSION' => $data->kubernetesVersion,
            'SSH_KEY_NAME' => $clusterName,
        ];

        $envString = collect($env)->map(fn (string $v, string $k): string => "{$k}={$v}")->implode(' ');

        $result = Process::timeout(30)->run(
            "{$envString} {$clusterctlPath} generate cluster {$clusterName} --infrastructure hetzner --kubeconfig {$kubeconfigPath}"
        );

        if ($result->failed()) {
            throw new RuntimeException("Failed to generate cluster manifests: {$result->errorOutput()}");
        }

        $apply = Process::timeout(60)->input($result->output())->run(
            "kubectl apply -f - --kubeconfig {$kubeconfigPath}"
        );

        if ($apply->failed()) {
            throw new RuntimeException("Failed to apply cluster manifests: {$apply->errorOutput()}");
        }
    }

    private function waitForClusterReady(string $clusterName, string $kubeconfigPath): void
    {
        $result = Process::timeout(900)->run(
            "kubectl wait cluster/{$clusterName} --for=condition=Ready --timeout=900s --namespace=default --kubeconfig {$kubeconfigPath}"
        );

        if ($result->failed()) {
            throw new RuntimeException("Timed out waiting for cluster [{$clusterName}] to become ready: {$result->errorOutput()}");
        }
    }

    private function getWorkloadKubeconfig(string $clusterName, string $kubeconfigPath, string $clusterctlPath): string
    {
        $result = Process::timeout(30)->run(
            "{$clusterctlPath} get kubeconfig {$clusterName} --namespace=default --kubeconfig {$kubeconfigPath}"
        );

        if ($result->failed()) {
            throw new RuntimeException("Failed to get kubeconfig for cluster [{$clusterName}]: {$result->errorOutput()}");
        }

        $targetKubeconfigPath = sys_get_temp_dir()."/{$clusterName}-target-kubeconfig";
        file_put_contents($targetKubeconfigPath, $result->output());

        return $targetKubeconfigPath;
    }

    private function pivotToHetznerCluster(string $sourceKubeconfigPath, string $targetKubeconfigPath, string $clusterctlPath): void
    {
        $result = Process::timeout(300)->run(
            "{$clusterctlPath} move --kubeconfig {$sourceKubeconfigPath} --to-kubeconfig {$targetKubeconfigPath}"
        );

        if ($result->failed()) {
            throw new RuntimeException("Failed to pivot CAPI state to Hetzner cluster: {$result->errorOutput()}");
        }
    }
}
