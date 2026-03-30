<?php

declare(strict_types=1);

namespace App\Queries;

use App\Enums\InfrastructureStatus;
use App\Models\CloudProvider;
use App\Models\Infrastructure;
use App\Models\Organization;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

final class InfrastructureQuery
{
    /** @var Builder<Infrastructure> */
    private Builder $builder;

    public function __invoke(): self
    {
        $this->builder = Infrastructure::query();

        return clone $this;
    }

    public function byId(string $id): self
    {
        $this->builder->where('id', $id);

        return $this;
    }

    public function byOrganization(Organization $organization): self
    {
        $this->builder->where('organization_id', $organization->id);

        return $this;
    }

    public function byProvider(CloudProvider $provider): self
    {
        $this->builder->where('cloud_provider_id', $provider->id);

        return $this;
    }

    public function byStatus(InfrastructureStatus $status): self
    {
        $this->builder->where('status', $status);

        return $this;
    }

    public function healthy(): self
    {
        $this->builder->where('status', InfrastructureStatus::Healthy);

        return $this;
    }

    public function provisioning(): self
    {
        $this->builder->where('status', InfrastructureStatus::Provisioning);

        return $this;
    }

    public function search(string $search): self
    {
        $this->builder->where(function (Builder $builder) use ($search): void {
            $builder->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        });

        return $this;
    }

    public function ordered(): self
    {
        $this->builder->orderBy('name');

        return $this;
    }

    /** @return Builder<Infrastructure> */
    public function builder(): Builder
    {
        return $this->builder;
    }

    public function first(): ?Infrastructure
    {
        /** @var Infrastructure|null */
        return $this->builder->first();
    }

    public function firstOrFail(): Infrastructure
    {
        /** @var Infrastructure */
        return $this->builder->firstOrFail();
    }

    /** @return Collection<int, Infrastructure> */
    public function get(): Collection
    {
        /** @var Collection<int, Infrastructure> */
        return $this->builder->get();
    }

    public function count(): int
    {
        return $this->builder->count();
    }

    /** @return LengthAwarePaginator<int, Infrastructure> */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        /** @var LengthAwarePaginator<int, Infrastructure> */
        return $this->builder->paginate($perPage);
    }
}
