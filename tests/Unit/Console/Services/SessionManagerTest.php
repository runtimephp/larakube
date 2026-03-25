<?php

declare(strict_types=1);

use App\Console\Services\SessionManager;
use App\Data\SessionOrganizationData;
use App\Data\SessionUserData;

beforeEach(function (): void {
    $this->tempDir = sys_get_temp_dir().'/session-manager-test-'.uniqid();
    mkdir($this->tempDir, 0700, true);
    $this->tempPath = $this->tempDir.'/session.json';
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
    $nestedPath = $this->tempDir.'/nested/deep/session.json';

    $manager = new SessionManager($nestedPath);
    $manager->set('key', 'value');

    expect(file_exists($nestedPath))->toBeTrue();

    // cleanup nested dirs
    unlink($nestedPath);
    rmdir(dirname($nestedPath));
    rmdir(dirname($nestedPath, 2));
});

test('setUser and getUser round-trip', function (): void {
    $manager = new SessionManager($this->tempPath);

    $userData = new SessionUserData(
        id: 'uuid-123',
        name: 'John Doe',
        email: 'john@example.com',
        token: 'token-abc',
    );

    $manager->setUser($userData);

    $retrieved = $manager->getUser();

    expect($retrieved)
        ->toBeInstanceOf(SessionUserData::class)
        ->id->toBe('uuid-123')
        ->name->toBe('John Doe')
        ->email->toBe('john@example.com')
        ->token->toBe('token-abc');
});

test('getUser returns null when empty', function (): void {
    $manager = new SessionManager($this->tempPath);

    expect($manager->getUser())->toBeNull();
});

test('setOrganization and getOrganization round-trip', function (): void {
    $manager = new SessionManager($this->tempPath);

    $orgData = new SessionOrganizationData(
        id: 'uuid-456',
        name: 'Acme Corp',
        slug: 'acme-corp',
    );

    $manager->setOrganization($orgData);

    $retrieved = $manager->getOrganization();

    expect($retrieved)
        ->toBeInstanceOf(SessionOrganizationData::class)
        ->id->toBe('uuid-456')
        ->name->toBe('Acme Corp')
        ->slug->toBe('acme-corp');
});

test('hasOrganization returns correct state', function (): void {
    $manager = new SessionManager($this->tempPath);

    expect($manager->hasOrganization())->toBeFalse();

    $manager->setOrganization(new SessionOrganizationData(
        id: 'uuid-456',
        name: 'Acme Corp',
        slug: 'acme-corp',
    ));

    expect($manager->hasOrganization())->toBeTrue();
});

test('setUser sets token for isAuthenticated', function (): void {
    $manager = new SessionManager($this->tempPath);

    expect($manager->isAuthenticated())->toBeFalse();

    $manager->setUser(new SessionUserData(
        id: 'uuid-123',
        name: 'John Doe',
        email: 'john@example.com',
        token: 'token-abc',
    ));

    expect($manager->isAuthenticated())->toBeTrue();
});

test('clearOrganization removes organization from session', function (): void {
    $manager = new SessionManager($this->tempPath);

    $manager->setOrganization(new SessionOrganizationData(
        id: 'uuid-456',
        name: 'Acme Corp',
        slug: 'acme-corp',
    ));

    expect($manager->hasOrganization())->toBeTrue();

    $manager->clearOrganization();

    expect($manager->hasOrganization())->toBeFalse();
});

test('clearInfrastructure removes infrastructure from session', function (): void {
    $manager = new SessionManager($this->tempPath);

    $manager->setInfrastructure(new App\Data\SessionInfrastructureData(
        id: 'uuid-789',
        name: 'Production',
    ));

    expect($manager->getInfrastructure())->not->toBeNull();

    $manager->clearInfrastructure();

    expect($manager->getInfrastructure())->toBeNull();
});
