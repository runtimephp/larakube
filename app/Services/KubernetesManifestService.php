<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ManifestService;
use App\Http\Integrations\Kubernetes\Contracts\ManifestContract;
use App\Http\Integrations\Kubernetes\Data\ManifestData;
use App\Http\Integrations\Kubernetes\KubernetesConnector;
use App\Http\Integrations\Kubernetes\Requests\ApplyManifest;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;

final readonly class KubernetesManifestService implements ManifestService
{
    public function __construct(
        private KubernetesConnector $connector,
    ) {}

    /**
     * @throws FatalRequestException
     * @throws RequestException
     */
    public function apply(ManifestContract $manifest): ManifestData
    {
        return $this->connector->send(new ApplyManifest($manifest))->dtoOrFail();
    }
}
