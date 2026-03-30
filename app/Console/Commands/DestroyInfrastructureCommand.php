<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\DestroyInfrastructure;
use App\Models\Organization;
use App\Queries\InfrastructureQuery;

final class DestroyInfrastructureCommand extends AuthenticatedCommand
{
    protected $signature = 'infrastructure:destroy';

    protected $description = 'Destroy all cloud resources for the selected infrastructure';

    protected bool $requiresOrganization = true;

    protected bool $requiresInfrastructure = true;

    public function handleCommand(InfrastructureQuery $query, DestroyInfrastructure $action): int
    {
        /** @var Organization $organization */
        $organization = Organization::query()->findOrFail($this->organization->id);

        $infrastructure = ($query)()
            ->byId($this->infrastructure->id)
            ->byOrganization($organization)
            ->first();

        if ($infrastructure === null) {
            $this->components->error('Infrastructure not found.');

            return self::FAILURE;
        }

        if (! $this->confirm('This will destroy ALL resources for infrastructure "'.$infrastructure->name.'". Continue?')) {
            $this->components->error('Aborted.');

            return self::FAILURE;
        }

        $this->components->info('Destroying infrastructure resources...');

        $failures = $action->handle($infrastructure);

        if ($failures !== []) {
            $this->components->warn('Destroyed with '.count($failures).' cloud API failure(s). Check cloud console for orphaned resources:');
            foreach ($failures as $failure) {
                $this->line("  - {$failure}");
            }

            return self::FAILURE;
        }

        $this->components->info("Infrastructure \"{$infrastructure->name}\" destroyed successfully.");

        return self::SUCCESS;
    }
}
