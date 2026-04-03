<?php

declare(strict_types=1);

test('landing page renders with waitlist count', function (): void {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('welcome')
        ->has('waitlistCount')
    );
});

test('user can join the waitlist', function (): void {
    $response = $this->post('/waitlist', [
        'email' => 'test@example.com',
    ]);

    $response->assertRedirect('/');
    $response->assertSessionHas('waitlist_success', true);

    $this->assertDatabaseHas('waitlist_entries', [
        'email' => 'test@example.com',
    ]);
});

test('duplicate email does not create a second entry', function (): void {
    $this->post('/waitlist', ['email' => 'test@example.com']);
    $this->post('/waitlist', ['email' => 'test@example.com']);

    $this->assertDatabaseCount('waitlist_entries', 1);
});

test('email is required to join the waitlist', function (): void {
    $response = $this->post('/waitlist', ['email' => '']);

    $response->assertSessionHasErrors('email');
    $this->assertDatabaseCount('waitlist_entries', 0);
});

test('email must be valid to join the waitlist', function (): void {
    $response = $this->post('/waitlist', ['email' => 'not-an-email']);

    $response->assertSessionHasErrors('email');
    $this->assertDatabaseCount('waitlist_entries', 0);
});
