<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ClusterTopology;
use App\Enums\InfrastructureStatus;
use App\Enums\ProvisioningPhase;
use Carbon\CarbonImmutable;
use Database\Factories\KubernetesClusterFactory;
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
 * @property-read string $infrastructure_id
 * @property-read string $name
 * @property-read string|null $version
 * @property-read string|null $external_cluster_id
 * @property-read InfrastructureStatus $status
 * @property-read string|null $kubeconfig
 * @property-read string|null $api_endpoint
 * @property-read string|null $pod_cidr
 * @property-read string|null $service_cidr
 * @property-read string|null $provisioning_step
 * @property-read ProvisioningPhase|null $provisioning_phase
 * @property-read ClusterTopology|null $topology
 * @property-read Infrastructure $infrastructure
 * @property-read Collection<int, Server> $nodes
 */
final class KubernetesCluster extends Model
{
    /** @use HasFactory<KubernetesClusterFactory> */
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
            'infrastructure_id' => 'string',
            'name' => 'string',
            'version' => 'string',
            'external_cluster_id' => 'string',
            'status' => InfrastructureStatus::class,
            'kubeconfig' => 'encrypted',
            'api_endpoint' => 'string',
            'pod_cidr' => 'string',
            'service_cidr' => 'string',
            'provisioning_step' => 'string',
            'provisioning_phase' => ProvisioningPhase::class,
            'topology' => ClusterTopology::class,
        ];
    }

    /** @return BelongsTo<Infrastructure, $this> */
    public function infrastructure(): BelongsTo
    {
        return $this->belongsTo(Infrastructure::class);
    }

    /** @return HasMany<Server, $this> */
    public function nodes(): HasMany
    {
        return $this->hasMany(Server::class, 'kubernetes_cluster_id');
    }
}
