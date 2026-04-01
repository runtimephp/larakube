<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Sleep;
use Illuminate\Support\Str;

pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->beforeEach(function (): void {
        Str::createRandomStringsNormally();
        Str::createUuidsNormally();
        Http::preventStrayRequests();
        Process::preventStrayProcesses();
        Sleep::fake();

        $this->withoutVite();
        $this->freezeTime();

        config()->set('inertia.ssr.enabled', false);
    })
    ->in('Feature', 'Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', fn () => $this->toBe(1));

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

/*
|--------------------------------------------------------------------------
| InMemory Service Helpers
|--------------------------------------------------------------------------
|
| Helper functions for binding InMemory services to the container.
| These allow tests to control service behavior without mocking.
|
*/

/**
 * Use InMemory HetznerService for validation testing.
 *
 * @param  bool|null  $isValid  Set validation result (true=valid, false=invalid, null=no override)
 */
function useInMemoryHetznerService(?bool $isValid = null): App\Services\InMemory\InMemoryHetznerService
{
    $service = new App\Services\InMemory\InMemoryHetznerService();

    if ($isValid !== null) {
        $service->setValidationResult($isValid);
    }

    return $service;
}

/**
 * Use InMemory HetznerServerService for server operations testing.
 */
function useInMemoryHetznerServerService(): App\Services\InMemory\InMemoryHetznerServerService
{
    return new App\Services\InMemory\InMemoryHetznerServerService();
}

/**
 * Use InMemory DigitalOceanService for validation testing.
 *
 * @param  bool|null  $isValid  Set validation result (true=valid, false=invalid, null=no override)
 */
function useInMemoryDigitalOceanService(?bool $isValid = null): App\Services\InMemory\InMemoryDigitalOceanService
{
    $service = new App\Services\InMemory\InMemoryDigitalOceanService();

    if ($isValid !== null) {
        $service->setValidationResult($isValid);
    }

    return $service;
}

/**
 * Use InMemory DigitalOceanServerService for server operations testing.
 */
function useInMemoryDigitalOceanServerService(): App\Services\InMemory\InMemoryDigitalOceanServerService
{
    return new App\Services\InMemory\InMemoryDigitalOceanServerService();
}

/**
 * Bind InMemory Hetzner services to CloudProviderFactory.
 *
 * @param  App\Services\InMemory\InMemoryHetznerService|null  $validationService  Service for token validation
 * @param  App\Services\InMemory\InMemoryHetznerServerService|null  $serverService  Service for server operations
 */
function bindInMemoryHetznerFactory(
    ?App\Services\InMemory\InMemoryHetznerService $validationService = null,
    ?App\Services\InMemory\InMemoryHetznerServerService $serverService = null
): void {
    $factory = new App\Services\InMemory\InMemoryHetznerFactory($validationService, $serverService);

    app()->instance(App\Services\CloudProviderFactory::class, $factory);
}

/**
 * Bind InMemory DigitalOcean services to CloudProviderFactory.
 *
 * @param  App\Services\InMemory\InMemoryDigitalOceanService|null  $validationService  Service for token validation
 * @param  App\Services\InMemory\InMemoryDigitalOceanServerService|null  $serverService  Service for server operations
 */
function bindInMemoryDigitalOceanFactory(
    ?App\Services\InMemory\InMemoryDigitalOceanService $validationService = null,
    ?App\Services\InMemory\InMemoryDigitalOceanServerService $serverService = null
): void {
    $factory = new App\Services\InMemory\InMemoryDigitalOceanFactory($validationService, $serverService);

    app()->instance(App\Services\CloudProviderFactory::class, $factory);
}

/**
 * Use InMemory MultipassService for validation testing.
 *
 * @param  bool|null  $isValid  Set validation result (true=valid, false=invalid, null=no override)
 */
function useInMemoryMultipassService(?bool $isValid = null): App\Services\InMemory\InMemoryMultipassService
{
    $service = new App\Services\InMemory\InMemoryMultipassService();

    if ($isValid !== null) {
        $service->setValidationResult($isValid);
    }

    return $service;
}

/**
 * Use InMemory MultipassServerService for server operations testing.
 */
function useInMemoryMultipassServerService(): App\Services\InMemory\InMemoryMultipassServerService
{
    return new App\Services\InMemory\InMemoryMultipassServerService();
}

/**
 * Bind InMemory Multipass services to CloudProviderFactory.
 *
 * @param  App\Services\InMemory\InMemoryMultipassService|null  $validationService  Service for validation
 * @param  App\Services\InMemory\InMemoryMultipassServerService|null  $serverService  Service for server operations
 */
function bindInMemoryMultipassFactory(
    ?App\Services\InMemory\InMemoryMultipassService $validationService = null,
    ?App\Services\InMemory\InMemoryMultipassServerService $serverService = null
): void {
    $factory = new App\Services\InMemory\InMemoryMultipassFactory($validationService, $serverService);

    app()->instance(App\Services\CloudProviderFactory::class, $factory);
}
