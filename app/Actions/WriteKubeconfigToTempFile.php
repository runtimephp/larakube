<?php

declare(strict_types=1);

namespace App\Actions;

use App\Contracts\KubeconfigReaderService;

final readonly class WriteKubeconfigToTempFile
{
    public function __construct(
        private KubeconfigReaderService $kubeconfigReaderService,
    ) {}

    public function handle(string $clusterName): string
    {
        $kubeconfig = $this->kubeconfigReaderService->read($clusterName);
        $path = sys_get_temp_dir()."/{$clusterName}-kubeconfig";

        file_put_contents($path, $kubeconfig);

        return $path;
    }
}
