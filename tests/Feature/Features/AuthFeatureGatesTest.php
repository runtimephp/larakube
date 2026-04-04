<?php

declare(strict_types=1);

use App\Features\LoginFeature;
use App\Features\RegistrationFeature;
use App\Models\User;
use Laravel\Pennant\Feature;

test('login page is always accessible', function (): void {
    Feature::deactivate(LoginFeature::class);

    $this->get('/login')->assertOk();
});

test('allowed email can login when feature is inactive', function (): void {
    Feature::deactivate(LoginFeature::class);
    config()->set('app.features.login_allowed_emails', ['francisco.barrento@gmail.com']);

    /** @var User $user */
    $user = User::factory()->create([
        'email' => 'francisco.barrento@gmail.com',
    ]);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect();

    $this->assertAuthenticated();
});

test('non-allowed email gets validation error when feature is inactive', function (): void {
    Feature::for('other@example.com')->deactivate(LoginFeature::class);
    config()->set('app.features.login_allowed_emails', ['francisco.barrento@gmail.com']);

    /** @var User $user */
    $user = User::factory()->create([
        'email' => 'other@example.com',
    ]);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertSessionHasErrors([
        'email' => 'Login is not available yet. Join the waitlist at kuven.io for early access.',
    ]);

    $this->assertGuest();
});

test('registration page is always accessible', function (): void {
    Feature::deactivate(RegistrationFeature::class);

    $this->get('/register')->assertOk();
});

test('registration succeeds when feature is active', function (): void {
    Feature::activate(RegistrationFeature::class);

    $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect();

    $this->assertAuthenticated();
});

test('registration returns validation error when feature is inactive', function (): void {
    Feature::for('test@example.com')->deactivate(RegistrationFeature::class);

    $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertSessionHasErrors([
        'email' => 'Registration is not available yet. Join the waitlist at kuven.io for early access.',
    ]);

    $this->assertGuest();
});

test('login feature resolves false in production', function (): void {
    app()->detectEnvironment(fn () => 'production');
    config()->set('app.features.login', false);
    config()->set('app.features.login_allowed_emails', []);

    $feature = app()->make(LoginFeature::class);

    expect($feature->resolve())->toBeFalse();
});

test('login feature resolves true for allowed email in production', function (): void {
    app()->detectEnvironment(fn () => 'production');
    config()->set('app.features.login', false);
    config()->set('app.features.login_allowed_emails', ['allowed@example.com']);

    $feature = app()->make(LoginFeature::class);

    expect($feature->resolve('allowed@example.com'))->toBeTrue();
    expect($feature->resolve('other@example.com'))->toBeFalse();
});

test('login feature resolves true when config enabled in production', function (): void {
    app()->detectEnvironment(fn () => 'production');
    config()->set('app.features.login', true);

    $feature = app()->make(LoginFeature::class);

    expect($feature->resolve())->toBeTrue();
});

test('registration feature resolves false in production', function (): void {
    app()->detectEnvironment(fn () => 'production');
    config()->set('app.features.registration', false);

    $feature = app()->make(RegistrationFeature::class);

    expect($feature->resolve())->toBeFalse();
});

test('registration feature resolves true when config enabled in production', function (): void {
    app()->detectEnvironment(fn () => 'production');
    config()->set('app.features.registration', true);

    $feature = app()->make(RegistrationFeature::class);

    expect($feature->resolve())->toBeTrue();
});

test('registration feature resolves true for allowed email in production', function (): void {
    app()->detectEnvironment(fn () => 'production');
    config()->set('app.features.registration', false);
    config()->set('app.features.registration_allowed_emails', ['allowed@example.com']);

    $feature = app()->make(RegistrationFeature::class);

    expect($feature->resolve('allowed@example.com'))->toBeTrue();
    expect($feature->resolve('other@example.com'))->toBeFalse();
});
