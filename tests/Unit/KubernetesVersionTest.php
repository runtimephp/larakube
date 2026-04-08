<?php

declare(strict_types=1);

use App\Casts\KubernetesVersionCast;
use App\Data\KubernetesVersionData;
use App\Enums\KubernetesVersion;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;

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

test('is supported returns true when end of life is in the future', function (): void {
    $data = new KubernetesVersionData(
        name: '1.99.0',
        releaseDate: CarbonImmutable::now()->subYear(),
        endOfLife: CarbonImmutable::now()->addYear(),
    );

    expect($data->isSupported())->toBeTrue()
        ->and($data->isEndOfLife())->toBeFalse();
});

test('is end of life returns true when end of life is in the past', function (): void {
    $data = new KubernetesVersionData(
        name: '1.0.0',
        releaseDate: CarbonImmutable::now()->subYears(2),
        endOfLife: CarbonImmutable::now()->subYear(),
    );

    expect($data->isEndOfLife())->toBeTrue()
        ->and($data->isSupported())->toBeFalse();
});

test('cast get returns null for null value', function (): void {
    $cast = new KubernetesVersionCast;

    $result = $cast->get(Mockery::mock(Model::class), 'version', null, []);

    expect($result)->toBeNull();
});

test('cast get throws for unknown version', function (): void {
    $cast = new KubernetesVersionCast;

    $cast->get(Mockery::mock(Model::class), 'version', 'invalid', []);
})->throws(InvalidArgumentException::class, 'Unknown Kubernetes version: invalid');

test('cast set returns null for null value', function (): void {
    $cast = new KubernetesVersionCast;

    $result = $cast->set(Mockery::mock(Model::class), 'version', null, []);

    expect($result)->toBeNull();
});

test('cast set extracts name from kubernetes version data', function (): void {
    $cast = new KubernetesVersionCast;
    $data = KubernetesVersion::V1_35_3->data();

    $result = $cast->set(Mockery::mock(Model::class), 'version', $data, []);

    expect($result)->toBe('1.35.3');
});
