<?php

declare(strict_types=1);

use App\Models\Provider;
use App\Queries\ProviderQuery;

test('returns a list of active providers', function (): void {

    /** @var Provider $activeProvider */
    $activeProvider = Provider::factory()
        ->active()
        ->hetzner()
        ->create();

    Provider::factory()
        ->akamai()
        ->create();

    $results = (new ProviderQuery())()
        ->active()
        ->get();

    expect($results)
        ->toHaveCount(1)
        ->and($results->first())
        ->name
        ->toBe($activeProvider->name);

});
