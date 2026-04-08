<?php

declare(strict_types=1);

use App\Data\KubernetesVersionData;
use App\Enums\KubernetesVersion;

test('each version returns a kubernetes version data object', function (): void {
    foreach (KubernetesVersion::cases() as $version) {
        $data = $version->data();

        expect($data)->toBeInstanceOf(KubernetesVersionData::class)
            ->and($data->name)->toBe($version->value)
            ->and($data->releaseDate->isBefore($data->endOfLife))->toBeTrue();
    }
});

test('label returns a human readable string', function (): void {
    expect(KubernetesVersion::V1_35_3->label())->toBe('Kubernetes 1.35.3');
});

test('supported returns only versions with future end of life', function (): void {
    $supported = KubernetesVersion::supported();

    expect($supported)->not->toBeEmpty();

    foreach ($supported as $version) {
        expect($version->data()->endOfLife->isFuture())->toBeTrue();
    }
});
