<?php

declare(strict_types=1);

use App\Services\MultipassService;
use Symfony\Component\Process\Process;

function fakeProcess(bool $successful = true, string $output = '', string $errorOutput = ''): Closure
{
    return function (array $command) use ($successful, $output): Process {
        $process = new Process(['echo', 'fake']);
        // We need a real process that returns controlled output
        // Use a simple command that succeeds or fails
        if ($successful) {
            $process = Process::fromShellCommandline('echo '.escapeshellarg($output));
        } else {
            $process = Process::fromShellCommandline('exit 1');
        }
        $process->run();

        return $process;
    };
}

test('validate token returns true when multipass binary exists', function (): void {
    $service = new MultipassService(fakeProcess(successful: true, output: 'multipass 1.14.0'));

    expect($service->validateToken())->toBeTrue();
});

test('validate token returns false when multipass binary not found', function (): void {
    $service = new MultipassService(fakeProcess(successful: false));

    expect($service->validateToken())->toBeFalse();
});
