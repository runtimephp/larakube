<?php

declare(strict_types=1);

use App\Services\CloudInitGenerator;
use Symfony\Component\Yaml\Yaml;

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
