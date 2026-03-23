<?php

declare(strict_types=1);

use App\Console\Services\SessionManager;

beforeEach(function (): void {
    $this->tempDir = sys_get_temp_dir() . '/session-manager-test-' . uniqid();
    mkdir($this->tempDir, 0700, true);
    $this->tempPath = $this->tempDir . '/session.json';
});

afterEach(function (): void {
    if (file_exists($this->tempPath)) {
        unlink($this->tempPath);
    }

    if (is_dir($this->tempDir)) {
        rmdir($this->tempDir);
    }
});

test('set and get a value', function (): void {
    $manager = new SessionManager($this->tempPath);

    $manager->set('token', 'abc123');

    expect($manager->get('token'))->toBe('abc123');
});

test('get returns default when key does not exist', function (): void {
    $manager = new SessionManager($this->tempPath);

    expect($manager->get('missing'))->toBeNull()
        ->and($manager->get('missing', 'fallback'))->toBe('fallback');
});

test('persists data to disk', function (): void {
    $manager = new SessionManager($this->tempPath);
    $manager->set('cluster', 'production');

    $freshManager = new SessionManager($this->tempPath);

    expect($freshManager->get('cluster'))->toBe('production');
});

test('clear removes all data', function (): void {
    $manager = new SessionManager($this->tempPath);
    $manager->set('token', 'abc123');
    $manager->set('cluster', 'production');

    $manager->clear();

    expect($manager->get('token'))->toBeNull()
        ->and($manager->get('cluster'))->toBeNull();
});

test('isAuthenticated returns true when token is set', function (): void {
    $manager = new SessionManager($this->tempPath);

    expect($manager->isAuthenticated())->toBeFalse();

    $manager->set('token', 'abc123');

    expect($manager->isAuthenticated())->toBeTrue();
});

test('isAuthenticated returns false after clear', function (): void {
    $manager = new SessionManager($this->tempPath);
    $manager->set('token', 'abc123');

    $manager->clear();

    expect($manager->isAuthenticated())->toBeFalse();
});

test('save creates directory if it does not exist', function (): void {
    $nestedPath = $this->tempDir . '/nested/deep/session.json';

    $manager = new SessionManager($nestedPath);
    $manager->set('key', 'value');

    expect(file_exists($nestedPath))->toBeTrue();

    // cleanup nested dirs
    unlink($nestedPath);
    rmdir(dirname($nestedPath));
    rmdir(dirname($nestedPath, 2));
});
