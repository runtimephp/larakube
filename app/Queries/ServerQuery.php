<?php

declare(strict_types=1);

namespace App\Queries;

use App\Enums\ServerRole;
use App\Enums\ServerStatus;
use App\Models\CloudProvider;
use App\Models\Infrastructure;
use App\Models\Organization;
use App\Models\Server;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

final class ServerQuery
{
    /** @var Builder<Server> */
    private Builder $builder;

    public function __invoke(): self
    {
        $this->builder = Server::query();

        return clone $this;
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

    public function byInfrastructure(Infrastructure $infrastructure): self
    {
        $this->builder->where('infrastructure_id', $infrastructure->id);

        return $this;
    }

    public function byRole(ServerRole $role): self
    {
        $this->builder->where('role', $role);

        return $this;
    }

    public function byStatus(ServerStatus $status): self
    {
        $this->builder->where('status', $status);

        return $this;
    }

    public function byRegion(string $region): self
    {
        $this->builder->where('region', $region);

        return $this;
    }

    public function byExternalId(string $externalId): self
    {
        $this->builder->where('external_id', $externalId);

        return $this;
    }

    public function running(): self
    {
        $this->builder->where('status', ServerStatus::Running);

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

    /** @return Builder<Server> */
    public function builder(): Builder
    {
        return $this->builder;
    }

    public function exists(): bool
    {
        return $this->builder->exists();
    }

    public function first(): ?Server
    {
        /** @var Server|null */
        return $this->builder->first();
    }

    public function firstOrFail(): Server
    {
        /** @var Server */
        return $this->builder->firstOrFail();
    }

    /** @return Collection<int, Server> */
    public function get(): Collection
    {
        /** @var Collection<int, Server> */
        return $this->builder->get();
    }

    public function count(): int
    {
        return $this->builder->count();
    }

    /** @return LengthAwarePaginator<int, Server> */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        /** @var LengthAwarePaginator<int, Server> */
        return $this->builder->paginate($perPage);
    }
}
