<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\InfrastructureStatus;
use App\Enums\ProvisioningPhase;
use App\Enums\ProvisioningStep;
use Carbon\CarbonImmutable;
use Database\Factories\InfrastructureFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read string $id
 * @property-read CarbonImmutable $created_at
 * @property-read CarbonImmutable $updated_at
 * @property-read string $organization_id
 * @property-read string $cloud_provider_id
 * @property-read string|null $region_id
 * @property-read string $name
 * @property-read string|null $description
 * @property-read InfrastructureStatus $status
 * @property-read ProvisioningStep|null $provisioning_step
 * @property-read ProvisioningPhase|null $provisioning_phase
 * @property-read Organization $organization
 * @property-read CloudProvider $cloudProvider
 * @property-read Region|null $region
 * @property-read Collection<int, KubernetesCluster> $kubernetesClusters
 * @property-read Collection<int, Network> $networks
 * @property-read Collection<int, Firewall> $firewalls
 * @property-read Collection<int, LoadBalancer> $loadBalancers
 * @property-read Collection<int, Storage> $storages
 * @property-read Collection<int, Backup> $backups
 * @property-read Collection<int, Server> $servers
 * @property-read Collection<int, SshKey> $sshKeys
 */
final class Infrastructure extends Model
{
    /** @use HasFactory<InfrastructureFactory> */
    use HasFactory;

    use HasUuids;

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'string',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
            'organization_id' => 'string',
            'cloud_provider_id' => 'string',
            'region_id' => 'string',
            'name' => 'string',
            'description' => 'string',
            'status' => InfrastructureStatus::class,
            'provisioning_step' => ProvisioningStep::class,
            'provisioning_phase' => ProvisioningPhase::class,
        ];
    }

    /** @return BelongsTo<Organization, $this> */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /** @return BelongsTo<CloudProvider, $this> */
    public function cloudProvider(): BelongsTo
    {
        return $this->belongsTo(CloudProvider::class);
    }

    /** @return BelongsTo<Region, $this> */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    /** @return HasMany<KubernetesCluster, $this> */
    public function kubernetesClusters(): HasMany
    {
        return $this->hasMany(KubernetesCluster::class);
    }

    /** @return HasMany<Network, $this> */
    public function networks(): HasMany
    {
        return $this->hasMany(Network::class);
    }

    /** @return HasMany<Firewall, $this> */
    public function firewalls(): HasMany
    {
        return $this->hasMany(Firewall::class);
    }

    /** @return HasMany<LoadBalancer, $this> */
    public function loadBalancers(): HasMany
    {
        return $this->hasMany(LoadBalancer::class);
    }

    /** @return HasMany<Storage, $this> */
    public function storages(): HasMany
    {
        return $this->hasMany(Storage::class);
    }

    /** @return HasMany<Backup, $this> */
    public function backups(): HasMany
    {
        return $this->hasMany(Backup::class);
    }

    /** @return HasMany<Server, $this> */
    public function servers(): HasMany
    {
        return $this->hasMany(Server::class);
    }

    /** @return HasMany<SshKey, $this> */
    public function sshKeys(): HasMany
    {
        return $this->hasMany(SshKey::class);
    }
}
