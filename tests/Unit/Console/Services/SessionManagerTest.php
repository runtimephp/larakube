<?php

declare(strict_types=1);

use App\Console\Services\SessionManager;
use App\Data\SessionOrganizationData;
use App\Data\SessionUserData;

beforeEach(function (): void {
    $this->sessionsPath = sys_get_temp_dir().'/larakube-sessions-test-'.uniqid();
    $this->apiUrl = 'http://localhost:8000';
});

afterEach(function (): void {
    $files = glob($this->sessionsPath.'/*.json');
    if (is_array($files)) {
        array_map(unlink(...), $files);
    }

    if (is_dir($this->sessionsPath)) {
        rmdir($this->sessionsPath);
    }
});

test('set and get a value', function (): void {
    $manager = new SessionManager($this->sessionsPath, $this->apiUrl);

    $manager->set('token', 'abc123');

    expect($manager->get('token'))->toBe('abc123');
});

test('get returns default when key does not exist', function (): void {
    $manager = new SessionManager($this->sessionsPath, $this->apiUrl);

    expect($manager->get('missing'))->toBeNull()
        ->and($manager->get('missing', 'fallback'))->toBe('fallback');
});

test('persists data to disk', function (): void {
    $manager = new SessionManager($this->sessionsPath, $this->apiUrl);
    $manager->set('cluster', 'production');

    $freshManager = new SessionManager($this->sessionsPath, $this->apiUrl);

    expect($freshManager->get('cluster'))->toBe('production');
});

test('clear removes all data', function (): void {
    $manager = new SessionManager($this->sessionsPath, $this->apiUrl);
    $manager->set('token', 'abc123');
    $manager->set('cluster', 'production');

    $manager->clear();

    expect($manager->get('token'))->toBeNull()
        ->and($manager->get('cluster'))->toBeNull();
});

test('isAuthenticated returns true when token is set', function (): void {
    $manager = new SessionManager($this->sessionsPath, $this->apiUrl);

    expect($manager->isAuthenticated())->toBeFalse();

    $manager->set('token', 'abc123');

    expect($manager->isAuthenticated())->toBeTrue();
});

test('isAuthenticated returns false after clear', function (): void {
    $manager = new SessionManager($this->sessionsPath, $this->apiUrl);
    $manager->set('token', 'abc123');

    $manager->clear();

    expect($manager->isAuthenticated())->toBeFalse();
});

test('save creates directory if it does not exist', function (): void {
    $nestedPath = $this->sessionsPath.'/nested/deep';

    $manager = new SessionManager($nestedPath, $this->apiUrl);
    $manager->set('key', 'value');

    expect(is_dir($nestedPath))->toBeTrue();

    // cleanup nested dirs
    array_map(unlink(...), glob($nestedPath.'/*.json'));
    rmdir($nestedPath);
    rmdir(dirname($nestedPath));
});

test('setUser and getUser round-trip', function (): void {
    $manager = new SessionManager($this->sessionsPath, $this->apiUrl);

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
    $manager = new SessionManager($this->sessionsPath, $this->apiUrl);

    expect($manager->getUser())->toBeNull();
});

test('setOrganization and getOrganization round-trip', function (): void {
    $manager = new SessionManager($this->sessionsPath, $this->apiUrl);

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
    $manager = new SessionManager($this->sessionsPath, $this->apiUrl);

    expect($manager->hasOrganization())->toBeFalse();

    $manager->setOrganization(new SessionOrganizationData(
        id: 'uuid-456',
        name: 'Acme Corp',
        slug: 'acme-corp',
    ));

    expect($manager->hasOrganization())->toBeTrue();
});

test('setUser sets token for isAuthenticated', function (): void {
    $manager = new SessionManager($this->sessionsPath, $this->apiUrl);

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
    $manager = new SessionManager($this->sessionsPath, $this->apiUrl);

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
    $manager = new SessionManager($this->sessionsPath, $this->apiUrl);

    $manager->setInfrastructure(new App\Data\SessionInfrastructureData(
        id: 'uuid-789',
        name: 'Production',
    ));

    expect($manager->getInfrastructure())->not->toBeNull();

    $manager->clearInfrastructure();

    expect($manager->getInfrastructure())->toBeNull();
});

// --- Per-URL session scoping tests ---

test('session file is scoped per api url', function (): void {
    $manager1 = new SessionManager($this->sessionsPath, 'http://localhost:8000');
    $manager2 = new SessionManager($this->sessionsPath, 'https://production.example.com');

    $manager1->setUser(new SessionUserData(
        id: 'user-1',
        name: 'Local User',
        email: 'local@example.com',
        token: 'local-token',
    ));

    $manager2->setUser(new SessionUserData(
        id: 'user-2',
        name: 'Production User',
        email: 'prod@example.com',
        token: 'prod-token',
    ));

    expect($manager1->getUser())
        ->name->toBe('Local User')
        ->and($manager2->getUser())
        ->name->toBe('Production User');
});

test('different urls produce different session files', function (): void {
    $manager1 = new SessionManager($this->sessionsPath, 'http://localhost:8000');
    $manager1->setUser(new SessionUserData(
        id: 'user-1',
        name: 'User 1',
        email: 'user1@example.com',
        token: 'token-1',
    ));

    $manager2 = new SessionManager($this->sessionsPath, 'http://localhost:9000');

    expect($manager2->getUser())->toBeNull()
        ->and($manager2->isAuthenticated())->toBeFalse();
});

test('clearing one session does not affect another', function (): void {
    $manager1 = new SessionManager($this->sessionsPath, 'http://localhost:8000');
    $manager2 = new SessionManager($this->sessionsPath, 'https://staging.example.com');

    $userData = new SessionUserData(
        id: 'user-1',
        name: 'User',
        email: 'user@example.com',
        token: 'token-1',
    );

    $manager1->setUser($userData);
    $manager2->setUser($userData);

    $manager1->clear();

    expect($manager1->isAuthenticated())->toBeFalse()
        ->and($manager2->isAuthenticated())->toBeTrue();
});
