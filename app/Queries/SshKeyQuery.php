<?php

declare(strict_types=1);

namespace App\Queries;

use App\Enums\SshKeyPurpose;
use App\Models\Infrastructure;
use App\Models\SshKey;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

final class SshKeyQuery
{
    /** @var Builder<SshKey> */
    private Builder $builder;

    public function __invoke(): self
    {
        $this->builder = SshKey::query();

        return clone $this;
    }

    public function byInfrastructure(Infrastructure $infrastructure): self
    {
        $this->builder->where('infrastructure_id', $infrastructure->id);

        return $this;
    }

    public function byPurpose(SshKeyPurpose $purpose): self
    {
        $this->builder->where('purpose', $purpose);

        return $this;
    }

    public function unregistered(): self
    {
        $this->builder->whereNull('external_ssh_key_id');

        return $this;
    }

    public function first(): ?SshKey
    {
        /** @var SshKey|null */
        return $this->builder->first();
    }

    public function firstOrFail(): SshKey
    {
        /** @var SshKey */
        return $this->builder->firstOrFail();
    }

    /** @return Collection<int, SshKey> */
    public function get(): Collection
    {
        /** @var Collection<int, SshKey> */
        return $this->builder->get();
    }

    public function exists(): bool
    {
        return $this->builder->exists();
    }
}
