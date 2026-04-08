<?php

declare(strict_types=1);

namespace App\Casts;

use App\Data\KubernetesVersionData;
use App\Enums\KubernetesVersion;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * @implements CastsAttributes<KubernetesVersionData, KubernetesVersion|KubernetesVersionData|string>
 */
final class KubernetesVersionCast implements CastsAttributes
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?KubernetesVersionData
    {
        if ($value === null) {
            return null;
        }

        $version = KubernetesVersion::tryFrom($value);

        if ($version === null) {
            throw new InvalidArgumentException("Unknown Kubernetes version: {$value}");
        }

        return $version->data();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof KubernetesVersionData) {
            return $value->name;
        }

        if ($value instanceof KubernetesVersion) {
            return $value->value;
        }

        return (string) $value;
    }
}
