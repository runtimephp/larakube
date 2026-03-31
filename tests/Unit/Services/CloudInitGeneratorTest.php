<?php

declare(strict_types=1);

use App\Services\CloudInitGenerator;
use Symfony\Component\Yaml\Yaml;

test('throws when template file does not exist', function (): void {
    $generator = new CloudInitGenerator(basePath: '/nonexistent/path');
    $generator->bastion(bastionPublicKey: 'ssh-ed25519 AAAA...');
})->throws(RuntimeException::class, 'Cloud-init template not found');

test('throws when template content is invalid yaml', function (): void {
    $tempDir = sys_get_temp_dir().'/cloudinit-test-'.uniqid();
    mkdir($tempDir);
    file_put_contents($tempDir.'/bastion.yaml', 'just a string');

    try {
        $generator = new CloudInitGenerator(basePath: $tempDir);
        $generator->bastion(bastionPublicKey: 'ssh-ed25519 AAAA...');
    } finally {
        @unlink($tempDir.'/bastion.yaml');
        @rmdir($tempDir);
    }
})->throws(RuntimeException::class, 'Invalid cloud-init template format');

test('uses custom basePath for template resolution', function (): void {
    $tempDir = sys_get_temp_dir().'/cloudinit-test-'.uniqid();
    mkdir($tempDir);
    file_put_contents($tempDir.'/bastion.yaml', "packages:\n  - curl\nruncmd:\n  - echo hello\n");

    try {
        $generator = new CloudInitGenerator(basePath: $tempDir);
        $yaml = $generator->bastion(bastionPublicKey: 'ssh-ed25519 AAAA...');

        expect($yaml)->toStartWith("#cloud-config\n")
            ->and($yaml)->toContain('ssh-ed25519 AAAA...');
    } finally {
        @unlink($tempDir.'/bastion.yaml');
        @rmdir($tempDir);
    }
});

test('throws on empty bastion public key', function (): void {
    $generator = new CloudInitGenerator();
    $generator->bastion(bastionPublicKey: '');
})->throws(InvalidArgumentException::class, 'Bastion public key must not be empty.');

test('throws on whitespace-only bastion public key', function (): void {
    $generator = new CloudInitGenerator();
    $generator->bastion(bastionPublicKey: '   ');
})->throws(InvalidArgumentException::class, 'Bastion public key must not be empty.');

test('generates cloud-init yaml with cloud-config header', function (): void {
    $generator = new CloudInitGenerator();
    $yaml = $generator->bastion(bastionPublicKey: 'ssh-ed25519 AAAA... kuven@bastion');

    expect($yaml)->toStartWith("#cloud-config\n");
});

test('includes bastion ssh public key in authorized keys', function (): void {
    $generator = new CloudInitGenerator();
    $yaml = $generator->bastion(bastionPublicKey: 'ssh-ed25519 AAAA... kuven@bastion');

    expect($yaml)->toContain('ssh-ed25519 AAAA... kuven@bastion');
});

test('installs ansible', function (): void {
    $generator = new CloudInitGenerator();
    $yaml = $generator->bastion(bastionPublicKey: 'ssh-ed25519 AAAA...');

    expect($yaml)->toContain('ansible');
});

test('installs kubectl', function (): void {
    $generator = new CloudInitGenerator();
    $yaml = $generator->bastion(bastionPublicKey: 'ssh-ed25519 AAAA...');

    expect($yaml)->toContain('kubectl');
});

test('installs helm', function (): void {
    $generator = new CloudInitGenerator();
    $yaml = $generator->bastion(bastionPublicKey: 'ssh-ed25519 AAAA...');

    expect($yaml)->toContain('helm');
});

test('throws on empty node public key', function (): void {
    $generator = new CloudInitGenerator();
    $generator->node(nodePublicKey: '');
})->throws(InvalidArgumentException::class, 'Node public key must not be empty.');

test('throws on whitespace-only node public key', function (): void {
    $generator = new CloudInitGenerator();
    $generator->node(nodePublicKey: '   ');
})->throws(InvalidArgumentException::class, 'Node public key must not be empty.');

test('generates node cloud-init without gateway', function (): void {
    $generator = new CloudInitGenerator();
    $yaml = $generator->node(nodePublicKey: 'ssh-ed25519 AAAA... node@key');

    expect($yaml)->toStartWith("#cloud-config\n")
        ->and($yaml)->toContain('ssh-ed25519 AAAA... node@key')
        ->and($yaml)->not->toContain('runcmd')
        ->and($yaml)->not->toContain('write_files');
});

test('generates node cloud-init with gateway', function (): void {
    $generator = new CloudInitGenerator();
    $yaml = $generator->node(
        nodePublicKey: 'ssh-ed25519 AAAA... node@key',
        networkGateway: '10.0.0.1',
    );

    expect($yaml)->toStartWith("#cloud-config\n")
        ->and($yaml)->toContain('ssh-ed25519 AAAA... node@key')
        ->and($yaml)->toContain('10.0.0.1')
        ->and($yaml)->toContain('runcmd')
        ->and($yaml)->toContain('write_files')
        ->and($yaml)->toContain('enp7s0');
});

test('generates node cloud-init with gateway and custom dns', function (): void {
    $generator = new CloudInitGenerator();
    $yaml = $generator->node(
        nodePublicKey: 'ssh-ed25519 AAAA... node@key',
        networkGateway: '10.0.0.1',
        dnsServers: ['185.12.64.1', '185.12.64.2'],
    );

    expect($yaml)->toContain('185.12.64.1 185.12.64.2');
});

test('generates node cloud-init with gateway uses default dns when empty', function (): void {
    $generator = new CloudInitGenerator();
    $yaml = $generator->node(
        nodePublicKey: 'ssh-ed25519 AAAA... node@key',
        networkGateway: '10.0.0.1',
        dnsServers: [],
    );

    expect($yaml)->toContain('1.1.1.1 8.8.8.8');
});

test('node cloud-init with gateway is valid yaml', function (): void {
    $generator = new CloudInitGenerator();
    $yaml = $generator->node(
        nodePublicKey: 'ssh-ed25519 AAAA...',
        networkGateway: '10.0.0.1',
    );

    $withoutHeader = mb_substr($yaml, mb_strlen("#cloud-config\n"));
    $parsed = Yaml::parse($withoutHeader);

    expect($parsed)->toBeArray()
        ->and($parsed)->toHaveKey('ssh_authorized_keys')
        ->and($parsed)->toHaveKey('runcmd')
        ->and($parsed)->toHaveKey('write_files');
});

test('output is valid yaml', function (): void {
    $generator = new CloudInitGenerator();
    $yaml = $generator->bastion(bastionPublicKey: 'ssh-ed25519 AAAA...');

    $withoutHeader = mb_substr($yaml, mb_strlen("#cloud-config\n"));
    $parsed = Yaml::parse($withoutHeader);

    expect($parsed)->toBeArray()
        ->and($parsed)->toHaveKey('packages')
        ->and($parsed)->toHaveKey('ssh_authorized_keys')
        ->and($parsed)->toHaveKey('runcmd');
});
