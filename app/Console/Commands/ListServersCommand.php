<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Contracts\ServerClient;
use App\Data\ServerResourceData;
use App\Enums\ServerStatus;
use App\Exceptions\LarakubeApiException;

final class ListServersCommand extends AuthenticatedCommand
{
    /**
     * @var string
     */
    protected $signature = 'server:list';

    /**
     * @var string
     */
    protected $description = 'List servers for the current organization';

    protected bool $requiresOrganization = true;

    public function handleCommand(ServerClient $serverClient): int
    {
        try {
            $servers = $serverClient->list();
        } catch (LarakubeApiException $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        if ($servers === []) {
            $this->components->info('No servers found.');

            return self::SUCCESS;
        }

        $this->table(
            ['Name', 'Status', 'Type', 'Region', 'IPv4'],
            array_map(fn (ServerResourceData $server): array => [
                $server->name,
                ServerStatus::from($server->status)->label(),
                $server->type,
                $server->region,
                $server->ipv4 ?? '-',
            ], $servers),
        );

        return self::SUCCESS;
    }
}
