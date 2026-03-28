<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Contracts\ServerClient;
use App\Enums\ServerStatus;
use App\Exceptions\LarakubeApiException;

use function Laravel\Prompts\text;

final class ShowServerCommand extends AuthenticatedCommand
{
    /**
     * @var string
     */
    protected $signature = 'server:show {--id= : Server ID}';

    /**
     * @var string
     */
    protected $description = 'Show details of a server';

    protected bool $requiresOrganization = true;

    public function handleCommand(ServerClient $serverClient): int
    {
        $serverId = $this->option('id') ?: text(
            label: 'Server ID',
            required: true,
        );

        try {
            $server = $serverClient->show($serverId);
        } catch (LarakubeApiException $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        $this->table(
            ['Field', 'Value'],
            [
                ['ID', $server->id],
                ['Name', $server->name],
                ['Status', ServerStatus::from($server->status)->label()],
                ['Type', $server->type],
                ['Region', $server->region],
                ['IPv4', $server->ipv4 ?? '-'],
                ['IPv6', $server->ipv6 ?? '-'],
                ['External ID', $server->externalId],
            ],
        );

        return self::SUCCESS;
    }
}
