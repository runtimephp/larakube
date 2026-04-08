<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\ManagementCluster;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

final class ManagementClusterQuery
{
    /** @var Builder<ManagementCluster> */
    private Builder $builder;

    public function __invoke(): self
    {
        $this->builder = ManagementCluster::query()->with(['provider', 'platformRegion']);

        return clone $this;
    }

    /** @return Collection<int, ManagementCluster> */
    public function get(): Collection
    {
        return $this->builder->get();
    }
}
