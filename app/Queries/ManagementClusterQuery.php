<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\ManagementCluster;
use Illuminate\Database\Eloquent\Builder;

final class ManagementClusterQuery
{
    /** @var Builder<ManagementCluster> */
    private Builder $builder;

    public function __invoke(): self
    {
        $this->builder = ManagementCluster::query();

        return clone $this;
    }

    public function byProvider(string $provider): self
    {
        $this->builder->where('provider', $provider);

        return $this;
    }

    public function byRegion(string $region): self
    {
        $this->builder->where('region', $region);

        return $this;
    }

    public function first(): ?ManagementCluster
    {
        return $this->builder->first();
    }
}
