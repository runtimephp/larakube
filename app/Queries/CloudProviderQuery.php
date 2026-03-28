<?php

declare(strict_types=1);

namespace App\Queries;

use App\Enums\CloudProviderType;
use App\Models\CloudProvider;
use App\Models\Organization;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

final class CloudProviderQuery
{
    /** @var Builder<CloudProvider> */
    private Builder $builder;

    public function __invoke(): self
    {
        $this->builder = CloudProvider::query();

        return clone $this;
    }

    public function byOrganization(Organization $organization): self
    {
        $this->builder->where('organization_id', $organization->id);

        return $this;
    }

    public function byType(CloudProviderType $type): self
    {
        $this->builder->where('type', $type);

        return $this;
    }

    public function verified(): self
    {
        $this->builder->where('is_verified', true);

        return $this;
    }

    public function unverified(): self
    {
        $this->builder->where('is_verified', false);

        return $this;
    }

    public function search(string $search): self
    {
        $this->builder->where('name', 'like', "%{$search}%");

        return $this;
    }

    public function ordered(): self
    {
        $this->builder->orderBy('name');

        return $this;
    }

    /** @return Builder<CloudProvider> */
    public function builder(): Builder
    {
        return $this->builder;
    }

    public function first(): ?CloudProvider
    {
        /** @var CloudProvider|null */
        return $this->builder->first();
    }

    /** @return Collection<int, CloudProvider> */
    public function get(): Collection
    {
        /** @var Collection<int, CloudProvider> */
        return $this->builder->get();
    }

    public function count(): int
    {
        return $this->builder->count();
    }

    /** @return LengthAwarePaginator<int, CloudProvider> */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        /** @var LengthAwarePaginator<int, CloudProvider> */
        return $this->builder->paginate($perPage);
    }
}
