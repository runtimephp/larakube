<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Contracts\ServerClient;
use App\Enums\ServerStatus;
use App\Exceptions\LarakubeApiException;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\select;

final class DeleteServerCommand extends AuthenticatedCommand
{
    /**
     * @var string
     */
    protected $signature = 'server:delete';

    /**
     * @var string
     */
    protected $description = 'Delete a server';

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
            $this->components->info('No servers to delete.');

            return self::SUCCESS;
        }

        $choices = [];
        foreach ($servers as $server) {
            $choices[$server->id] = "{$server->name} (".ServerStatus::from($server->status)->label().')';
        }

        $selectedId = select(
            label: 'Select a server to delete',
            options: $choices,
        );

        $selected = null;
        foreach ($servers as $server) {
            if ($server->id === $selectedId) {
                $selected = $server;
                break;
            }
        }

        if (! confirm("Are you sure you want to delete [{$selected->name}]?")) {
            $this->components->info('Cancelled.');

            return self::SUCCESS;
        }

        try {
            $serverClient->delete($selected->id);
        } catch (LarakubeApiException $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        $this->components->info("Server [{$selected->name}] deleted.");

        return self::SUCCESS;
    }
}
