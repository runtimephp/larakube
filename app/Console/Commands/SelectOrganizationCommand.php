<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\Services\SessionManager;
use App\Data\SessionOrganizationData;
use App\Models\User;

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

    public function handleCommand(SessionManager $session): int
    {
        /** @var User|null $user */
        $user = User::query()->find($this->user->id);
        $organizations = $user->organizations;

        if ($organizations->isEmpty()) {
            $this->components->error('You do not belong to any organizations. Run [organization:create] first.');

            return self::FAILURE;
        }

        $choices = $organizations->pluck('name', 'id')->toArray();

        $selectedId = select(
            label: 'Select an organization',
            options: $choices,
        );

        $selected = $organizations->firstWhere('id', $selectedId);

        $session->setOrganization(new SessionOrganizationData(
            id: $selected->id,
            name: $selected->name,
            slug: $selected->slug,
        ));

        $this->components->info("Selected organization [{$selected->name}].");

        return self::SUCCESS;
    }
}
