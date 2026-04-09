<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\Provider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

final class ProviderQuery
{
    /** @var Builder<Provider> */
    private Builder $builder;

    public function __invoke(): self
    {
        $this->builder = Provider::query();

        return clone $this;
    }

    /**
     * @param  array<int, string>  $relations
     */
    public function with(array $relations): self
    {
        $this->builder->with($relations);

        return $this;
    }

    public function orderBy(string $column = 'name', string $direction = 'asc'): self
    {
        $this->builder->orderBy($column, $direction);

        return $this;
    }

    public function active(): self
    {
        $this->builder->where('is_active', true);

        return $this;
    }

    /** @return Collection<int, Provider> */
    public function get(): Collection
    {
        return $this->builder->get();
    }
}
