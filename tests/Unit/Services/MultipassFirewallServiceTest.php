<?php

declare(strict_types=1);

use App\Data\CreateFirewallData;
use App\Data\FirewallRuleData;
use App\Services\MultipassFirewallService;

test('create returns firewall data with provided values', function (): void {
    $service = new MultipassFirewallService();
    $firewall = $service->create(new CreateFirewallData(
        name: 'k8s-firewall',
        infrastructure_id: '00000000-0000-0000-0000-000000000001',
    ));

    expect($firewall->name)->toBe('k8s-firewall')
        ->and($firewall->externalId)->toBe('multipass-k8s-firewall')
        ->and($firewall->rules)->toBeEmpty();
});

test('add rule returns firewall data with matching external id', function (): void {
    $service = new MultipassFirewallService();
    $firewall = $service->addRule('any-id', new FirewallRuleData(
        direction: 'in',
        protocol: 'tcp',
        portStart: 443,
        portEnd: 443,
        sourceIps: ['0.0.0.0/0'],
    ));

    expect($firewall->externalId)->toBe('any-id');
});

test('list returns empty collection', function (): void {
    $service = new MultipassFirewallService();

    expect($service->list())->toBeEmpty();
});

test('find returns null', function (): void {
    $service = new MultipassFirewallService();

    expect($service->find('any-id'))->toBeNull();
});

test('delete returns true', function (): void {
    $service = new MultipassFirewallService();

    expect($service->delete('any-id'))->toBeTrue();
});
