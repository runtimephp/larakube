<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\Services\SessionManager;
use App\Contracts\OrganizationClient;
use App\Data\CreateOrganizationData;
use App\Data\SessionOrganizationData;
use App\Exceptions\LarakubeApiException;

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

    public function handleCommand(SessionManager $session, OrganizationClient $organizationClient): int
    {
        $name = $this->option('name') ?: text(
            label: 'Organization name',
            required: true,
        );

        $description = $this->option('description') ?: text(
            label: 'Description',
        );

        try {
            $organization = $organizationClient->create(
                new CreateOrganizationData(
                    name: $name,
                    description: $description ?: null,
                ),
            );
        } catch (LarakubeApiException $e) {
            $this->components->error($e->getMessage());

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
