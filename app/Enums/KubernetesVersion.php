<?php

declare(strict_types=1);

namespace App\Enums;

use App\Data\KubernetesVersionData;
use Carbon\CarbonImmutable;

enum KubernetesVersion: string
{
    case V1_35_3 = '1.35.3';
    case V1_35_2 = '1.35.2';
    case V1_35_1 = '1.35.1';
    case V1_35_0 = '1.35.0';
    case V1_34_6 = '1.34.6';

    /**
     * @return array<int, self>
     */
    public static function supported(): array
    {
        return array_values(array_filter(
            self::cases(),
            fn (self $version) => $version->isSupported(),
        ));
    }

    public function label(): string
    {
        return "Kubernetes {$this->value}";
    }

    public function data(): KubernetesVersionData
    {
        return match ($this) {
            self::V1_35_3 => new KubernetesVersionData(
                name: $this->value,
                releaseDate: CarbonImmutable::parse('2026-03-19'),
                endOfLife: CarbonImmutable::parse('2027-02-28'),
            ),
            self::V1_35_2 => new KubernetesVersionData(
                name: $this->value,
                releaseDate: CarbonImmutable::parse('2026-02-26'),
                endOfLife: CarbonImmutable::parse('2027-02-28'),
            ),
            self::V1_35_1 => new KubernetesVersionData(
                name: $this->value,
                releaseDate: CarbonImmutable::parse('2026-02-10'),
                endOfLife: CarbonImmutable::parse('2027-02-28'),
            ),
            self::V1_35_0 => new KubernetesVersionData(
                name: $this->value,
                releaseDate: CarbonImmutable::parse('2025-12-17'),
                endOfLife: CarbonImmutable::parse('2027-02-28'),
            ),
            self::V1_34_6 => new KubernetesVersionData(
                name: $this->value,
                releaseDate: CarbonImmutable::parse('2025-08-27'),
                endOfLife: CarbonImmutable::parse('2026-10-27'),
            ),
        };
    }

    public function isSupported(): bool
    {
        return $this->data()->endOfLife->isFuture();
    }
}
