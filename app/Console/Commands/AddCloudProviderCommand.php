<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\CreateCloudProvider;
use App\Data\CreateCloudProviderData;
use App\Enums\CloudProviderType;
use App\Models\Organization;
use Throwable;

use function Laravel\Prompts\password;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

final class AddCloudProviderCommand extends AuthenticatedCommand
{
    /**
     * @var string
     */
    protected $signature = 'cloud-provider:add';

    /**
     * @var string
     */
    protected $description = 'Add a cloud provider to the current organization';

    protected bool $requiresOrganization = true;

    public function handleCommand(CreateCloudProvider $createCloudProvider): int
    {
        $typeOptions = [];
        foreach (CloudProviderType::cases() as $case) {
            $typeOptions[$case->value] = $case->label();
        }

        $typeValue = select(
            label: 'Select a cloud provider',
            options: $typeOptions,
        );

        $type = CloudProviderType::from($typeValue);

        $name = text(
            label: 'Name for this provider',
            placeholder: "e.g. {$type->label()} Production",
            required: true,
        );

        $apiToken = password(
            label: 'API token',
            required: true,
        );

        $organization = Organization::query()->find($this->organization->id);

        $this->components->info('Validating API token...');

        try {
            $cloudProvider = $createCloudProvider->handle(
                new CreateCloudProviderData(
                    name: $name,
                    type: $type,
                    apiToken: $apiToken,
                ),
                $organization,
            );
        } catch (Throwable $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        $this->components->info("Cloud provider [{$cloudProvider->name}] added successfully.");

        return self::SUCCESS;
    }
}
