<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ServerService;
use App\Data\CreateServerData;
use App\Data\ServerData;
use App\Enums\ServerStatus;
use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Process;

final readonly class MultipassServerService implements ServerService
{
    /** @var Closure(list<string>): Process */
    private Closure $processFactory;

    /**
     * @param  Closure(list<string>): Process|null  $processFactory
     */
    public function __construct(?Closure $processFactory = null)
    {
        $this->processFactory = $processFactory ?? fn (array $command): Process => new Process($command);
    }

    public function getAll(): Collection
    {
        $process = ($this->processFactory)(['multipass', 'list', '--format', 'json']);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new RuntimeException('Failed to list Multipass VMs: '.$process->getErrorOutput());
        }

        $data = json_decode($process->getOutput(), true);
        $instances = $data['list'] ?? [];

        return collect($instances)->map(fn (array $instance): ServerData => $this->mapInstanceData($instance));
    }

    public function create(CreateServerData $data): ServerData
    {
        $name = $data->name.'-'.Str::random(6);

        $command = ['multipass', 'launch', $data->image, '--name', $name];

        if ($data->cpus !== null) {
            $command[] = '--cpus';
            $command[] = (string) $data->cpus;
        }

        if ($data->memory !== null) {
            $command[] = '--memory';
            $command[] = $data->memory;
        }

        if ($data->disk !== null) {
            $command[] = '--disk';
            $command[] = $data->disk;
        }

        $process = ($this->processFactory)($command);
        $process->setTimeout(300);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new RuntimeException('Failed to create Multipass VM: '.$process->getErrorOutput());
        }

        $info = $this->getInstanceInfo($name);

        return $this->mapInstanceData($info);
    }

    public function find(string $name): ?ServerData
    {
        $info = $this->getInstanceInfo($name);

        if ($info === null) {
            return null;
        }

        return $this->mapInstanceData($info);
    }

    public function destroy(int|string $externalId): bool
    {
        $delete = ($this->processFactory)(['multipass', 'delete', (string) $externalId]);
        $delete->run();

        if (! $delete->isSuccessful()) {
            return false;
        }

        $purge = ($this->processFactory)(['multipass', 'purge']);
        $purge->run();

        return $purge->isSuccessful();
    }

    /**
     * @return array<string, mixed>|null
     */
    private function getInstanceInfo(string $name): ?array
    {
        $process = ($this->processFactory)(['multipass', 'info', $name, '--format', 'json']);
        $process->run();

        if (! $process->isSuccessful()) {
            return null;
        }

        $data = json_decode($process->getOutput(), true);
        $instances = $data['info'] ?? [];

        if (! isset($instances[$name])) {
            return null;
        }

        $instance = $instances[$name];
        $instance['name'] = $name;

        return $instance;
    }

    /**
     * @param  array<string, mixed>  $instance
     */
    private function mapInstanceData(array $instance): ServerData
    {
        $ipv4 = $instance['ipv4'][0] ?? $instance['ipv4'] ?? null;

        return new ServerData(
            externalId: $instance['name'],
            name: $instance['name'],
            status: ServerStatus::fromMultipass($instance['state'] ?? 'Unknown'),
            type: 'custom',
            region: 'local',
            ipv4: is_array($ipv4) ? null : $ipv4,
        );
    }
}
