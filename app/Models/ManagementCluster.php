<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ManagementClusterStatus;
use Carbon\CarbonImmutable;
use Database\Factories\ManagementClusterFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read string $id
 * @property-read CarbonImmutable $created_at
 * @property-read CarbonImmutable $updated_at
 * @property-read string $name
 * @property-read string $region
 * @property-read string $provider
 * @property-read string|null $kubeconfig
 * @property-read ManagementClusterStatus $status
 */
final class ManagementCluster extends Model
{
    /** @use HasFactory<ManagementClusterFactory> */
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
            'name' => 'string',
            'region' => 'string',
            'provider' => 'string',
            'kubeconfig' => 'encrypted',
            'status' => ManagementClusterStatus::class,
        ];
    }
}
