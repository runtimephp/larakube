<?php

declare(strict_types=1);

namespace App\Actions;

use App\Contracts\ClusterManifestGenerator;
use App\Contracts\ManifestService;
use App\Data\CreateClusterManifestData;

final readonly class ProvisionCluster
{
    public function __construct(
        private ClusterManifestGenerator $generator,
        private ManifestService $manifestService,
    ) {}

    public function handle(CreateClusterManifestData $data): void
    {
        $manifests = $this->generator->generate($data);

        foreach ($manifests as $manifest) {
            $this->manifestService->apply($manifest);
        }
    }
}
