<?php

declare(strict_types=1);

use App\Services\CloudInitGenerator;
use Symfony\Component\Yaml\Yaml;

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

test('generates node ssh keypair on bastion', function (): void {
    $generator = new CloudInitGenerator();
    $yaml = $generator->bastion(bastionPublicKey: 'ssh-ed25519 AAAA...');

    expect($yaml)->toContain('ssh-keygen')
        ->and($yaml)->toContain('ed25519');
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
