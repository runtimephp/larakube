<?php

declare(strict_types=1);

use App\Data\CreateFirewallData;
use App\Data\FirewallRuleData;
use App\Services\HetznerFirewallService;
use Illuminate\Support\Facades\Http;

test('create sends destination ips for outbound rules', function (): void {
    Http::fake([
        'api.hetzner.cloud/v1/firewalls' => Http::response([
            'firewall' => [
                'id' => 201,
                'name' => 'k8s-firewall',
                'rules' => [
                    [
                        'direction' => 'out',
                        'protocol' => 'tcp',
                        'port' => '443',
                        'destination_ips' => ['0.0.0.0/0'],
                    ],
                ],
            ],
        ]),
    ]);

    $service = new HetznerFirewallService('token');
    $firewall = $service->create(new CreateFirewallData(
        name: 'k8s-firewall',
        infrastructure_id: '00000000-0000-0000-0000-000000000001',
        rules: [
            new FirewallRuleData(
                direction: 'out',
                protocol: 'tcp',
                portStart: 443,
                portEnd: 443,
                destinationIps: ['0.0.0.0/0'],
            ),
        ],
    ));

    expect($firewall->rules[0]->direction)->toBe('out')
        ->and($firewall->rules[0]->destinationIps)->toBe(['0.0.0.0/0']);

    Http::assertSent(function ($request) {
        $rules = $request->data()['rules'] ?? [];

        return count($rules) === 1
            && $rules[0]['direction'] === 'out'
            && $rules[0]['destination_ips'] === ['0.0.0.0/0']
            && ! array_key_exists('source_ips', $rules[0]);
    });
});

test('create handles icmp rule without port', function (): void {
    Http::fake([
        'api.hetzner.cloud/v1/firewalls' => Http::response([
            'firewall' => [
                'id' => 101,
                'name' => 'k8s-firewall',
                'rules' => [
                    [
                        'direction' => 'in',
                        'protocol' => 'icmp',
                        'source_ips' => ['0.0.0.0/0'],
                    ],
                ],
            ],
        ]),
    ]);

    $service = new HetznerFirewallService('token');
    $firewall = $service->create(new CreateFirewallData(
        name: 'k8s-firewall',
        infrastructure_id: '00000000-0000-0000-0000-000000000001',
        rules: [
            new FirewallRuleData(
                direction: 'in',
                protocol: 'icmp',
                portStart: null,
                portEnd: null,
                sourceIps: ['0.0.0.0/0'],
            ),
        ],
    ));

    expect($firewall->rules[0]->protocol)->toBe('icmp')
        ->and($firewall->rules[0]->portStart)->toBeNull()
        ->and($firewall->rules[0]->portEnd)->toBeNull();

    Http::assertSent(function ($request) {
        $rules = $request->data()['rules'] ?? [];

        return count($rules) === 1 && ! array_key_exists('port', $rules[0]);
    });
});

test('create handles port range in rules', function (): void {
    Http::fake([
        'api.hetzner.cloud/v1/firewalls' => Http::response([
            'firewall' => [
                'id' => 789,
                'name' => 'k8s-firewall',
                'rules' => [
                    [
                        'direction' => 'in',
                        'protocol' => 'tcp',
                        'port' => '30000-32767',
                        'source_ips' => ['0.0.0.0/0'],
                    ],
                ],
            ],
        ]),
    ]);

    $service = new HetznerFirewallService('token');
    $firewall = $service->create(new CreateFirewallData(
        name: 'k8s-firewall',
        infrastructure_id: '00000000-0000-0000-0000-000000000001',
        rules: [
            new FirewallRuleData(
                direction: 'in',
                protocol: 'tcp',
                portStart: 30000,
                portEnd: 32767,
                sourceIps: ['0.0.0.0/0'],
            ),
        ],
    ));

    expect($firewall->rules[0]->portStart)->toBe(30000)
        ->and($firewall->rules[0]->portEnd)->toBe(32767);
});

test('create throws on api error', function (): void {
    Http::fake([
        'api.hetzner.cloud/v1/firewalls' => Http::response([
            'error' => ['message' => 'invalid input', 'code' => 'invalid_input'],
        ], 422),
    ]);

    $service = new HetznerFirewallService('token');

    $service->create(new CreateFirewallData(
        name: 'k8s-firewall',
        infrastructure_id: '00000000-0000-0000-0000-000000000001',
    ));
})->throws(RuntimeException::class, 'invalid input');

test('create returns firewall data with rules', function (): void {
    Http::fake([
        'api.hetzner.cloud/v1/firewalls' => Http::response([
            'firewall' => [
                'id' => 123,
                'name' => 'k8s-firewall',
                'rules' => [
                    [
                        'direction' => 'in',
                        'protocol' => 'tcp',
                        'port' => '443',
                        'source_ips' => ['0.0.0.0/0'],
                    ],
                ],
            ],
        ]),
    ]);

    $service = new HetznerFirewallService('token');
    $firewall = $service->create(new CreateFirewallData(
        name: 'k8s-firewall',
        infrastructure_id: '00000000-0000-0000-0000-000000000001',
        rules: [
            new FirewallRuleData(
                direction: 'in',
                protocol: 'tcp',
                portStart: 443,
                portEnd: 443,
                sourceIps: ['0.0.0.0/0'],
            ),
        ],
    ));

    expect($firewall->externalId)->toBe(123)
        ->and($firewall->name)->toBe('k8s-firewall')
        ->and($firewall->rules)->toHaveCount(1)
        ->and($firewall->rules[0]->portStart)->toBe(443)
        ->and($firewall->rules[0]->portEnd)->toBe(443);
});

test('add rule returns updated firewall data', function (): void {
    Http::fake([
        'api.hetzner.cloud/v1/firewalls/123/actions/set_rules' => Http::response([
            'actions' => [['id' => 1]],
        ]),
        'api.hetzner.cloud/v1/firewalls/123' => Http::response([
            'firewall' => [
                'id' => 123,
                'name' => 'k8s-firewall',
                'rules' => [
                    [
                        'direction' => 'in',
                        'protocol' => 'tcp',
                        'port' => '443',
                        'source_ips' => ['0.0.0.0/0'],
                    ],
                    [
                        'direction' => 'in',
                        'protocol' => 'tcp',
                        'port' => '80',
                        'source_ips' => ['0.0.0.0/0'],
                    ],
                ],
            ],
        ]),
    ]);

    $service = new HetznerFirewallService('token');
    $firewall = $service->addRule('123', new FirewallRuleData(
        direction: 'in',
        protocol: 'tcp',
        portStart: 80,
        portEnd: 80,
        sourceIps: ['0.0.0.0/0'],
    ));

    expect($firewall->rules)->toHaveCount(2);
});

test('add rule throws on api error', function (): void {
    Http::fake([
        'api.hetzner.cloud/v1/firewalls/123' => Http::response([
            'firewall' => [
                'id' => 123,
                'name' => 'k8s-firewall',
                'rules' => [],
            ],
        ]),
        'api.hetzner.cloud/v1/firewalls/123/actions/set_rules' => Http::response([
            'error' => ['message' => 'forbidden', 'code' => 'forbidden'],
        ], 403),
    ]);

    $service = new HetznerFirewallService('token');

    $service->addRule('123', new FirewallRuleData(
        direction: 'in',
        protocol: 'tcp',
        portStart: 80,
        portEnd: 80,
        sourceIps: ['0.0.0.0/0'],
    ));
})->throws(RuntimeException::class, 'forbidden');

test('list returns collection of firewall data', function (): void {
    Http::fake([
        'api.hetzner.cloud/v1/firewalls' => Http::response([
            'firewalls' => [
                [
                    'id' => 123,
                    'name' => 'k8s-firewall',
                    'rules' => [],
                ],
            ],
        ]),
    ]);

    $service = new HetznerFirewallService('token');
    $firewalls = $service->list();

    expect($firewalls)->toHaveCount(1)
        ->and($firewalls[0]->externalId)->toBe(123);
});

test('list throws on api error', function (): void {
    Http::fake([
        'api.hetzner.cloud/v1/firewalls' => Http::response([
            'error' => ['message' => 'unauthorized', 'code' => 'unauthorized'],
        ], 401),
    ]);

    $service = new HetznerFirewallService('token');

    $service->list();
})->throws(RuntimeException::class, 'unauthorized');

test('find returns firewall data', function (): void {
    Http::fake([
        'api.hetzner.cloud/v1/firewalls/123' => Http::response([
            'firewall' => [
                'id' => 123,
                'name' => 'k8s-firewall',
                'rules' => [],
            ],
        ]),
    ]);

    $service = new HetznerFirewallService('token');
    $firewall = $service->find('123');

    expect($firewall)->not->toBeNull()
        ->and($firewall->name)->toBe('k8s-firewall');
});

test('find returns null when not found', function (): void {
    Http::fake([
        'api.hetzner.cloud/v1/firewalls/999' => Http::response([
            'error' => ['message' => 'not found', 'code' => 'not_found'],
        ], 404),
    ]);

    $service = new HetznerFirewallService('token');

    expect($service->find('999'))->toBeNull();
});

test('find throws on non-404 api error', function (): void {
    Http::fake([
        'api.hetzner.cloud/v1/firewalls/123' => Http::response([
            'error' => ['message' => 'unauthorized', 'code' => 'unauthorized'],
        ], 401),
    ]);

    $service = new HetznerFirewallService('token');

    $service->find('123');
})->throws(RuntimeException::class, 'unauthorized');

test('delete returns true on success', function (): void {
    Http::fake([
        'api.hetzner.cloud/v1/firewalls/123' => Http::response([], 200),
    ]);

    $service = new HetznerFirewallService('token');

    expect($service->delete('123'))->toBeTrue();
});

test('delete returns false on failure', function (): void {
    Http::fake([
        'api.hetzner.cloud/v1/firewalls/999' => Http::response([], 404),
    ]);

    $service = new HetznerFirewallService('token');

    expect($service->delete('999'))->toBeFalse();
});
