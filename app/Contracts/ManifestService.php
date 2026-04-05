<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Http\Integrations\Kubernetes\Contracts\ManifestContract;
use App\Http\Integrations\Kubernetes\Data\ManifestData;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;

interface ManifestService
{
    /**
     * @throws FatalRequestException
     * @throws RequestException
     */
    public function apply(ManifestContract $manifest): ManifestData;
}
