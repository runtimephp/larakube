<?php

declare(strict_types=1);

use App\Enums\ServerRole;
use App\Enums\ServerStatus;
use App\Exceptions\RetryStepException;
use App\Models\Infrastructure;
use App\Models\Server;
use App\Models\SshKey;
use App\Queries\ServerQuery;
use App\Queries\SshKeyQuery;
use App\Services\BastionSshExecutor;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

beforeEach(function (): void {
    /** @var Infrastructure $this->infrastructure */
    $this->infrastructure = Infrastructure::factory()->provisioning()->createQuietly();

    Server::factory()->createQuietly([
        'infrastructure_id' => $this->infrastructure->id,
        'role' => ServerRole::Bastion,
        'status' => ServerStatus::Running,
        'ipv4' => '192.168.1.1',
    ]);

    SshKey::factory()->bastion()->createQuietly([
        'infrastructure_id' => $this->infrastructure->id,
    ]);
});

test('execute runs ssh command and returns output',
    /**
     * @throws Throwable
     */
    function (): void {
        Storage::fake('local');

        $processFactory = function (array $command): Process {
            $process = Process::fromShellCommandline('echo "hello world"');
            $process->run();

            return $process;
        };

        $ssh = new BastionSshExecutor(new ServerQuery(), new SshKeyQuery(), $processFactory);
        $output = $ssh->execute($this->infrastructure, 'echo hello');

        expect(mb_trim($output))->toBe('hello world');
    });

test('execute throws retry on connection refused',
    /**
     * @throws Throwable
     */
    function (): void {
        Storage::fake('local');

        $processFactory = function (array $command): Process {
            $process = Process::fromShellCommandline('echo "Connection refused" >&2 && exit 1');
            $process->run();

            return $process;
        };

        $ssh = new BastionSshExecutor(new ServerQuery(), new SshKeyQuery(), $processFactory);
        $ssh->execute($this->infrastructure, 'test');
    })->throws(RetryStepException::class, 'SSH connection failed');

test('execute throws retry on connection timed out',
    /**
     * @throws Throwable
     */
    function (): void {
        Storage::fake('local');

        $processFactory = function (array $command): Process {
            $process = Process::fromShellCommandline('echo "Connection timed out" >&2 && exit 1');
            $process->run();

            return $process;
        };

        $ssh = new BastionSshExecutor(new ServerQuery(), new SshKeyQuery(), $processFactory);
        $ssh->execute($this->infrastructure, 'test');
    })->throws(RetryStepException::class, 'SSH connection failed');

test('execute throws runtime exception on other failures',
    /**
     * @throws Throwable
     */
    function (): void {
        Storage::fake('local');

        $processFactory = function (array $command): Process {
            $process = Process::fromShellCommandline('echo "Permission denied" >&2 && exit 1');
            $process->run();

            return $process;
        };

        $ssh = new BastionSshExecutor(new ServerQuery(), new SshKeyQuery(), $processFactory);
        $ssh->execute($this->infrastructure, 'test');
    })->throws(RuntimeException::class, 'SSH command failed');

test('execute passes output to callback',
    /**
     * @throws Throwable
     */
    function (): void {
        Storage::fake('local');

        $processFactory = function (array $command): Process {
            $process = Process::fromShellCommandline('echo "callback test"');
            $process->run();

            return $process;
        };

        $capturedOutput = [];
        $ssh = new BastionSshExecutor(new ServerQuery(), new SshKeyQuery(), $processFactory);
        $ssh->execute($this->infrastructure, 'test', onOutput: function (string $type, string $buffer) use (&$capturedOutput): void {
            $capturedOutput[] = ['type' => $type, 'buffer' => $buffer];
        });

        expect($capturedOutput)->not->toBeEmpty();
    });

test('scp transfers file to bastion',
    /**
     * @throws Throwable
     */
    function (): void {
        $capturedCommands = [];
        $processFactory = function (array $command) use (&$capturedCommands): Process {
            $capturedCommands[] = $command;
            $process = Process::fromShellCommandline('true');
            $process->run();

            return $process;
        };

        $ssh = new BastionSshExecutor(new ServerQuery(), new SshKeyQuery(), $processFactory);
        $ssh->scp($this->infrastructure, '/tmp/test.ini', '/root/playbooks/hosts.ini');

        expect($capturedCommands)->toHaveCount(1);

        $commandStr = implode(' ', $capturedCommands[0]);
        expect($commandStr)->toContain('scp')
            ->and($commandStr)->toContain('192.168.1.1');
    });

test('scp with recursive flag',
    /**
     * @throws Throwable
     */
    function (): void {
        $capturedCommands = [];
        $processFactory = function (array $command) use (&$capturedCommands): Process {
            $capturedCommands[] = $command;
            $process = Process::fromShellCommandline('true');
            $process->run();

            return $process;
        };

        $ssh = new BastionSshExecutor(new ServerQuery(), new SshKeyQuery(), $processFactory);
        $ssh->scp($this->infrastructure, '/tmp/dir/', '/root/playbooks/', recursive: true);

        $commandStr = implode(' ', $capturedCommands[0]);
        expect($commandStr)->toContain('-r');
    });

test('scp throws retry on connection refused',
    /**
     * @throws Throwable
     */
    function (): void {
        $processFactory = function (array $command): Process {
            $process = Process::fromShellCommandline('echo "Connection refused" >&2 && exit 1');
            $process->run();

            return $process;
        };

        $ssh = new BastionSshExecutor(new ServerQuery(), new SshKeyQuery(), $processFactory);
        $ssh->scp($this->infrastructure, '/tmp/test.ini', '/root/hosts.ini');
    })->throws(RetryStepException::class, 'SCP connection failed');

test('scp throws runtime exception on other failures',
    /**
     * @throws Throwable
     */
    function (): void {
        $processFactory = function (array $command): Process {
            $process = Process::fromShellCommandline('echo "Permission denied" >&2 && exit 1');
            $process->run();

            return $process;
        };

        $ssh = new BastionSshExecutor(new ServerQuery(), new SshKeyQuery(), $processFactory);
        $ssh->scp($this->infrastructure, '/tmp/test.ini', '/root/hosts.ini');
    })->throws(RuntimeException::class, 'SCP failed');

test('throws when bastion has no ip',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->provisioning()->createQuietly();

        Server::factory()->createQuietly([
            'infrastructure_id' => $infrastructure->id,
            'role' => ServerRole::Bastion,
            'status' => ServerStatus::Running,
            'ipv4' => null,
        ]);

        SshKey::factory()->bastion()->createQuietly([
            'infrastructure_id' => $infrastructure->id,
        ]);

        $processFactory = fn (array $command): Process => Process::fromShellCommandline('true');

        $ssh = new BastionSshExecutor(new ServerQuery(), new SshKeyQuery(), $processFactory);
        $ssh->execute($infrastructure, 'test');
    })->throws(RuntimeException::class, 'Bastion server has no IP address');

test('streams logs to storage',
    /**
     * @throws Throwable
     */
    function (): void {
        Storage::fake('local');

        $processFactory = function (array $command): Process {
            $process = Process::fromShellCommandline('echo "log output"');
            $process->run();

            return $process;
        };

        $ssh = new BastionSshExecutor(new ServerQuery(), new SshKeyQuery(), $processFactory);
        $ssh->execute($this->infrastructure, 'test');

        $logPath = "bastion-logs/{$this->infrastructure->id}.log";
        Storage::disk('local')->assertExists($logPath);
    });
