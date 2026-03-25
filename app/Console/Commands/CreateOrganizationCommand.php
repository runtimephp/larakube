<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\CreateOrganization;
use App\Console\Services\SessionManager;
use App\Data\CreateOrganizationData;
use App\Data\SessionOrganizationData;
use App\Models\User;
use Throwable;

use function Laravel\Prompts\text;

final class CreateOrganizationCommand extends AuthenticatedCommand
{
    /**
     * @var string
     */
    protected $signature = 'organization:create {--name= : Organization name} {--description= : Organization description}';

    /**
     * @var string
     */
    protected $description = 'Create a new organization';

    public function handleCommand(SessionManager $session, CreateOrganization $createOrganization): int
    {
        $name = $this->option('name') ?: text(
            label: 'Organization name',
            required: true,
        );

        $description = $this->option('description') ?: text(
            label: 'Description',
        );

        $owner = User::query()->find($this->user->id);

        try {
            $organization = $createOrganization->handle(
                new CreateOrganizationData(
                    name: $name,
                    description: $description ?: null,
                ),
                $owner,
            );
        } catch (Throwable $e) {
            $this->components->error("Failed to create organization: {$e->getMessage()}");

            return self::FAILURE;
        }

        $session->setOrganization(new SessionOrganizationData(
            id: $organization->id,
            name: $organization->name,
            slug: $organization->slug,
        ));

        $this->components->info("Organization [{$organization->name}] created and selected.");

        return self::SUCCESS;
    }
}
