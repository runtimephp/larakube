<?php

declare(strict_types=1);

use App\Data\FirewallRuleData;

test('from port string throws on non-numeric port', function (): void {
    FirewallRuleData::fromPortString('in', 'tcp', 'abc');
})->throws(InvalidArgumentException::class);

test('from port string throws on non-numeric range start', function (): void {
    FirewallRuleData::fromPortString('in', 'tcp', 'abc-443');
})->throws(InvalidArgumentException::class);

test('from port string throws on non-numeric range end', function (): void {
    FirewallRuleData::fromPortString('in', 'tcp', '80-abc');
})->throws(InvalidArgumentException::class);

test('from port string throws on port below 1', function (): void {
    FirewallRuleData::fromPortString('in', 'tcp', '0');
})->throws(InvalidArgumentException::class);

test('from port string throws on port above 65535', function (): void {
    FirewallRuleData::fromPortString('in', 'tcp', '70000');
})->throws(InvalidArgumentException::class);

test('from port string throws when start exceeds end', function (): void {
    FirewallRuleData::fromPortString('in', 'tcp', '443-80');
})->throws(InvalidArgumentException::class);

test('from port string parses single port', function (): void {
    $rule = FirewallRuleData::fromPortString('in', 'tcp', '443');

    expect($rule->portStart)->toBe(443)
        ->and($rule->portEnd)->toBe(443);
});

test('from port string parses range', function (): void {
    $rule = FirewallRuleData::fromPortString('in', 'tcp', '30000-32767');

    expect($rule->portStart)->toBe(30000)
        ->and($rule->portEnd)->toBe(32767);
});

test('from port string handles null port for icmp', function (): void {
    $rule = FirewallRuleData::fromPortString('in', 'icmp', null);

    expect($rule->portStart)->toBeNull()
        ->and($rule->portEnd)->toBeNull();
});

test('to port string returns null for null ports', function (): void {
    $rule = new FirewallRuleData(
        direction: 'in',
        protocol: 'icmp',
        portStart: null,
        portEnd: null,
    );

    expect($rule->toPortString())->toBeNull();
});
