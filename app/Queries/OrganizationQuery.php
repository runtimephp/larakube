<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

final class OrganizationQuery
{
    /** @var Builder<Organization> */
    private Builder $builder;

    public function __invoke(): self
    {
        $this->builder = Organization::query();

        return clone $this;
    }

    public function byUser(User $user): self
    {
        $this->builder->whereHas('users', function (Builder $builder) use ($user): void {
            $builder->where('users.id', $user->id);
        });

        return $this;
    }

    public function bySlug(string $slug): self
    {
        $this->builder->where('slug', $slug);

        return $this;
    }

    public function search(string $search): self
    {
        $this->builder->where(function (Builder $builder) use ($search): void {
            $builder->where('name', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%");
        });

        return $this;
    }

    public function ordered(): self
    {
        $this->builder->orderBy('name');

        return $this;
    }

    /** @return Builder<Organization> */
    public function builder(): Builder
    {
        return $this->builder;
    }

    public function first(): ?Organization
    {
        /** @var Organization|null */
        return $this->builder->first();
    }

    /** @return Collection<int, Organization> */
    public function get(): Collection
    {
        /** @var Collection<int, Organization> */
        return $this->builder->get();
    }

    public function count(): int
    {
        return $this->builder->count();
    }

    /** @return LengthAwarePaginator<int, Organization> */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        /** @var LengthAwarePaginator<int, Organization> */
        return $this->builder->paginate($perPage);
    }
}
