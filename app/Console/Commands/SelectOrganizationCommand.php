<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\Services\SessionManager;
use App\Contracts\OrganizationClient;
use App\Data\SessionOrganizationData;
use App\Exceptions\LarakubeApiException;

use function Laravel\Prompts\select;

final class SelectOrganizationCommand extends AuthenticatedCommand
{
    /**
     * @var string
     */
    protected $signature = 'organization:select';

    /**
     * @var string
     */
    protected $description = 'Select an organization to work with';

    public function handleCommand(SessionManager $session, OrganizationClient $organizationClient): int
    {
        try {
            $organizations = $organizationClient->list();
        } catch (LarakubeApiException $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        if ($organizations === []) {
            $this->components->error('You do not belong to any organizations. Run [organization:create] first.');

            return self::FAILURE;
        }

        $choices = [];
        foreach ($organizations as $org) {
            $choices[$org->id] = $org->name;
        }

        $selectedId = select(
            label: 'Select an organization',
            options: $choices,
        );

        $selected = null;
        foreach ($organizations as $org) {
            if ($org->id === $selectedId) {
                $selected = $org;
                break;
            }
        }

        $session->setOrganization(new SessionOrganizationData(
            id: $selected->id,
            name: $selected->name,
            slug: $selected->slug,
        ));

        $this->components->info("Selected organization [{$selected->name}].");

        return self::SUCCESS;
    }
}
