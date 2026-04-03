<?php

declare(strict_types=1);

use App\Features\LoginFeature;
use App\Features\RegistrationFeature;
use App\Models\User;
use Laravel\Pennant\Feature;

test('registration page is accessible when feature is active', function (): void {
    Feature::activate(RegistrationFeature::class);

    $this->get('/register')->assertOk();
});

test('registration page returns 404 when feature is inactive', function (): void {
    Feature::deactivate(RegistrationFeature::class);

    $this->get('/register')->assertNotFound();
});

test('registration post returns 404 when feature is inactive', function (): void {
    Feature::deactivate(RegistrationFeature::class);

    $this->post('/register', [
        'name' => 'Test',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertNotFound();
});

test('login page is accessible when feature is active', function (): void {
    Feature::activate(LoginFeature::class);

    $this->get('/login')->assertOk();
});

test('login page returns 404 when feature is inactive', function (): void {
    Feature::deactivate(LoginFeature::class);

    $this->get('/login')->assertNotFound();
});

test('allowed email can login even when feature is inactive', function (): void {
    Feature::deactivate(LoginFeature::class);
    config()->set('app.features.login_allowed_emails', 'francisco.barrento@gmail.com');

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

test('non-allowed email cannot login when feature is inactive', function (): void {
    Feature::for('other@example.com')->deactivate(LoginFeature::class);
    config()->set('app.features.login_allowed_emails', 'francisco.barrento@gmail.com');

    /** @var User $user */
    $user = User::factory()->create([
        'email' => 'other@example.com',
    ]);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertNotFound();
});
