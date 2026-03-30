<?php

declare(strict_types=1);

use App\Data\ServerSpecData;
use App\Enums\CloudProviderType;

test('ssh user returns correct user per provider', function (): void {
    expect(CloudProviderType::Hetzner->sshUser())->toBe('root')
        ->and(CloudProviderType::DigitalOcean->sshUser())->toBe('root')
        ->and(CloudProviderType::Multipass->sshUser())->toBe('ubuntu');
});

test('bastion spec returns hetzner defaults', function (): void {
    $spec = CloudProviderType::Hetzner->bastionSpec();

    expect($spec)->toBeInstanceOf(ServerSpecData::class)
        ->and($spec->type)->toBe('cpx22')
        ->and($spec->image)->toBe('ubuntu-24.04')
        ->and($spec->region)->toBe('hel1')
        ->and($spec->cpus)->toBeNull()
        ->and($spec->memory)->toBeNull()
        ->and($spec->disk)->toBeNull();
});

test('bastion spec returns multipass defaults', function (): void {
    $spec = CloudProviderType::Multipass->bastionSpec();

    expect($spec)->toBeInstanceOf(ServerSpecData::class)
        ->and($spec->type)->toBe('custom')
        ->and($spec->image)->toBe('noble')
        ->and($spec->region)->toBe('local')
        ->and($spec->cpus)->toBe(1)
        ->and($spec->memory)->toBe('1G')
        ->and($spec->disk)->toBe('10G');
});

test('control plane spec returns hetzner defaults', function (): void {
    $spec = CloudProviderType::Hetzner->controlPlaneSpec();

    expect($spec)->toBeInstanceOf(ServerSpecData::class)
        ->and($spec->type)->toBe('cpx32')
        ->and($spec->image)->toBe('ubuntu-24.04');
});

test('control plane spec returns multipass defaults', function (): void {
    $spec = CloudProviderType::Multipass->controlPlaneSpec();

    expect($spec)->toBeInstanceOf(ServerSpecData::class)
        ->and($spec->cpus)->toBe(2)
        ->and($spec->memory)->toBe('4G')
        ->and($spec->disk)->toBe('20G');
});

test('worker spec returns hetzner defaults', function (): void {
    $spec = CloudProviderType::Hetzner->workerSpec();

    expect($spec)->toBeInstanceOf(ServerSpecData::class)
        ->and($spec->type)->toBe('cpx32');
});

test('worker spec returns multipass defaults', function (): void {
    $spec = CloudProviderType::Multipass->workerSpec();

    expect($spec)->toBeInstanceOf(ServerSpecData::class)
        ->and($spec->cpus)->toBe(2)
        ->and($spec->memory)->toBe('2G')
        ->and($spec->disk)->toBe('20G');
});
