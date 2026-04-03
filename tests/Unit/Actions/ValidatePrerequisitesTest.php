<?php

declare(strict_types=1);

use App\Actions\ValidatePrerequisites;
use App\Contracts\PrerequisiteChecker;
use App\Services\InMemory\InMemoryPrerequisiteChecker;

beforeEach(function (): void {
    $this->checker = new InMemoryPrerequisiteChecker;
    $this->app->instance(PrerequisiteChecker::class, $this->checker);
    $this->action = $this->app->make(ValidatePrerequisites::class);
});

test('passes when all prerequisites are met',
    /**
     * @throws Throwable
     */
    function (): void {
        $this->checker->setAvailable(['kind', 'clusterctl', 'kubectl', 'docker']);

        $result = $this->action->handle();

        expect($result->ok)->toBeTrue()
            ->and($result->missing)->toBe([]);
    });

test('fails when a binary is missing',
    /**
     * @throws Throwable
     */
    function (): void {
        $this->checker->setAvailable(['kind', 'kubectl', 'docker']);

        $result = $this->action->handle();

        expect($result->ok)->toBeFalse()
            ->and($result->missing)->toBe(['clusterctl']);
    });

test('fails when docker is not running',
    /**
     * @throws Throwable
     */
    function (): void {
        $this->checker->setAvailable(['kind', 'clusterctl', 'kubectl']);
        $this->checker->setDockerRunning(false);

        $result = $this->action->handle();

        expect($result->ok)->toBeFalse()
            ->and($result->missing)->toContain('docker');
    });

test('reports all missing prerequisites at once',
    /**
     * @throws Throwable
     */
    function (): void {
        $this->checker->setAvailable([]);

        $result = $this->action->handle();

        expect($result->ok)->toBeFalse()
            ->and($result->missing)->toBe(['kind', 'clusterctl', 'kubectl', 'docker']);
    });
