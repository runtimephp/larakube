<?php

declare(strict_types=1);

use App\Providers\HorizonServiceProvider;
use Illuminate\Support\Facades\Gate;

test('registers viewHorizon gate', function (): void {
    $provider = new HorizonServiceProvider($this->app);
    $provider->boot();

    expect(Gate::has('viewHorizon'))->toBeTrue();
});

test('viewHorizon gate denies null user', function (): void {
    $provider = new HorizonServiceProvider($this->app);
    $provider->boot();

    $result = Gate::check('viewHorizon');

    expect($result)->toBeFalse();
});
