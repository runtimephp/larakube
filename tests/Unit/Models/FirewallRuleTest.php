<?php

declare(strict_types=1);

use App\Models\Firewall;
use App\Models\FirewallRule;
use Carbon\CarbonImmutable;

test('creates firewall rule with port range',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var FirewallRule $rule */
        $rule = FirewallRule::factory()->createQuietly([
            'direction' => 'in',
            'protocol' => 'tcp',
            'port_start' => 30000,
            'port_end' => 32767,
        ]);

        expect($rule->direction)->toBe('in')
            ->and($rule->protocol)->toBe('tcp')
            ->and($rule->port_start)->toBe(30000)
            ->and($rule->port_end)->toBe(32767);
    });

test('creates firewall rule with single port',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var FirewallRule $rule */
        $rule = FirewallRule::factory()->createQuietly([
            'direction' => 'in',
            'protocol' => 'tcp',
            'port_start' => 443,
            'port_end' => 443,
        ]);

        expect($rule->port_start)->toBe(443)
            ->and($rule->port_end)->toBe(443)
            ->and($rule->id)->toBeString()
            ->and($rule->created_at)->toBeInstanceOf(CarbonImmutable::class);
    });

test('belongs to firewall',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Firewall $firewall */
        $firewall = Firewall::factory()->createQuietly();

        /** @var FirewallRule $rule */
        $rule = FirewallRule::factory()->createQuietly([
            'firewall_id' => $firewall->id,
        ]);

        expect($rule->firewall)->toBeInstanceOf(Firewall::class)
            ->and($rule->firewall->id)->toBe($firewall->id);
    });

test('casts source ips as array',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var FirewallRule $rule */
        $rule = FirewallRule::factory()->createQuietly([
            'source_ips' => ['10.0.0.0/16', '0.0.0.0/0'],
        ]);

        expect($rule->source_ips)->toBe(['10.0.0.0/16', '0.0.0.0/0']);
    });

test('uses uuid for primary key',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var FirewallRule $rule */
        $rule = FirewallRule::factory()->createQuietly();

        expect($rule->id)
            ->toBeString()
            ->toBeUuid();
    });

test('to array has all fields in correct order',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var FirewallRule $rule */
        $rule = FirewallRule::factory()
            ->createQuietly()
            ->refresh();

        expect(array_keys($rule->toArray()))
            ->toBe([
                'id',
                'created_at',
                'updated_at',
                'firewall_id',
                'direction',
                'protocol',
                'port_start',
                'port_end',
                'source_ips',
            ]);
    });
