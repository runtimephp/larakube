<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

final class UserQuery
{
    /** @var Builder<User> */
    private Builder $builder;

    public function __invoke(): self
    {
        $this->builder = User::query();

        return clone $this;
    }

    public function byEmail(string $email): self
    {
        $this->builder->where('email', $email);

        return $this;
    }

    public function verified(): self
    {
        $this->builder->whereNotNull('email_verified_at');

        return $this;
    }

    public function unverified(): self
    {
        $this->builder->whereNull('email_verified_at');

        return $this;
    }

    public function search(string $search): self
    {
        $this->builder->where(function (Builder $builder) use ($search): void {
            $builder->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        });

        return $this;
    }

    public function ordered(): self
    {
        $this->builder->orderBy('name');

        return $this;
    }

    /** @return Builder<User> */
    public function builder(): Builder
    {
        return $this->builder;
    }

    public function first(): ?User
    {
        /** @var User|null */
        return $this->builder->first();
    }

    /** @return Collection<int, User> */
    public function get(): Collection
    {
        /** @var Collection<int, User> */
        return $this->builder->get();
    }

    public function count(): int
    {
        return $this->builder->count();
    }

    /** @return LengthAwarePaginator<int, User> */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        /** @var LengthAwarePaginator<int, User> */
        return $this->builder->paginate($perPage);
    }
}
