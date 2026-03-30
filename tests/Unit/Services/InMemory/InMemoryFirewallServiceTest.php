<?php

declare(strict_types=1);

use App\Data\CreateFirewallData;
use App\Data\FirewallData;
use App\Data\FirewallRuleData;
use App\Services\InMemory\InMemoryFirewallService;

test('add rule throws when firewall not found', function (): void {
    $service = new InMemoryFirewallService();

    $service->addRule('nonexistent', new FirewallRuleData(
        direction: 'in',
        protocol: 'tcp',
        portStart: 80,
        portEnd: 80,
        sourceIps: ['0.0.0.0/0'],
    ));
})->throws(RuntimeException::class, 'not found');

test('delete throws when configured to throw on delete', function (): void {
    $service = new InMemoryFirewallService();
    $service->addFirewall(new FirewallData(externalId: '123', name: 'k8s-firewall'));
    $service->shouldThrowOnDelete();

    $service->delete('123');
})->throws(RuntimeException::class, 'Simulated API failure on delete');

test('create stores and returns firewall data', function (): void {
    $service = new InMemoryFirewallService();
    $firewall = $service->create(new CreateFirewallData(
        name: 'k8s-firewall',
        infrastructure_id: '00000000-0000-0000-0000-000000000001',
        rules: [
            new FirewallRuleData(direction: 'in', protocol: 'tcp', portStart: 443, portEnd: 443, sourceIps: ['0.0.0.0/0']),
        ],
    ));

    expect($firewall->name)->toBe('k8s-firewall')
        ->and($firewall->rules)->toHaveCount(1)
        ->and($service->list())->toHaveCount(1);
});

test('create throws when configured to fail', function (): void {
    $service = new InMemoryFirewallService();
    $service->shouldFailCreate();

    $service->create(new CreateFirewallData(
        name: 'k8s-firewall',
        infrastructure_id: '00000000-0000-0000-0000-000000000001',
    ));
})->throws(RuntimeException::class);

test('add rule appends rule to existing firewall', function (): void {
    $service = new InMemoryFirewallService();
    $service->addFirewall(new FirewallData(externalId: '123', name: 'k8s-firewall', rules: [
        new FirewallRuleData(direction: 'in', protocol: 'tcp', portStart: 443, portEnd: 443, sourceIps: ['0.0.0.0/0']),
    ]));

    $firewall = $service->addRule('123', new FirewallRuleData(
        direction: 'in',
        protocol: 'tcp',
        portStart: 80,
        portEnd: 80,
        sourceIps: ['0.0.0.0/0'],
    ));

    expect($firewall->rules)->toHaveCount(2)
        ->and($firewall->rules[1]->portStart)->toBe(80);
});

test('list returns all firewalls', function (): void {
    $service = new InMemoryFirewallService();
    $service->addFirewall(new FirewallData(externalId: '1', name: 'fw-1'));
    $service->addFirewall(new FirewallData(externalId: '2', name: 'fw-2'));

    expect($service->list())->toHaveCount(2);
});

test('find returns firewall by id', function (): void {
    $service = new InMemoryFirewallService();
    $service->addFirewall(new FirewallData(externalId: '123', name: 'k8s-firewall'));

    $firewall = $service->find('123');

    expect($firewall)->not->toBeNull()
        ->and($firewall->name)->toBe('k8s-firewall');
});

test('find returns null when not found', function (): void {
    $service = new InMemoryFirewallService();

    expect($service->find('nonexistent'))->toBeNull();
});

test('delete removes firewall and returns true', function (): void {
    $service = new InMemoryFirewallService();
    $service->addFirewall(new FirewallData(externalId: '123', name: 'k8s-firewall'));

    expect($service->delete('123'))->toBeTrue()
        ->and($service->list())->toBeEmpty();
});

test('delete returns false when not found', function (): void {
    $service = new InMemoryFirewallService();

    expect($service->delete('nonexistent'))->toBeFalse();
});

test('delete returns false when configured to fail', function (): void {
    $service = new InMemoryFirewallService();
    $service->addFirewall(new FirewallData(externalId: '123', name: 'k8s-firewall'));
    $service->shouldFailDelete();

    expect($service->delete('123'))->toBeFalse();
});
