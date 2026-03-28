<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Contracts\CloudProviderClient;
use App\Data\CreateCloudProviderData;
use App\Enums\CloudProviderType;
use App\Exceptions\LarakubeApiException;
use ValueError;

use function Laravel\Prompts\password;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

final class AddCloudProviderCommand extends AuthenticatedCommand
{
    /**
     * @var string
     */
    protected $signature = 'cloud-provider:add {--type= : Cloud provider type (hetzner, digitalocean)} {--name= : Provider name} {--token= : API token}';

    /**
     * @var string
     */
    protected $description = 'Add a cloud provider to the current organization';

    protected bool $requiresOrganization = true;

    public function handleCommand(CloudProviderClient $cloudProviderClient): int
    {
        $typeOption = $this->option('type');

        if ($typeOption) {
            try {
                $type = CloudProviderType::from($typeOption);
            } catch (ValueError) {
                $this->components->error('Invalid provider type. Use: hetzner, digitalocean');

                return self::FAILURE;
            }
        } else {
            $typeOptions = [];
            foreach (CloudProviderType::cases() as $case) {
                $typeOptions[$case->value] = $case->label();
            }

            $typeValue = select(
                label: 'Select a cloud provider',
                options: $typeOptions,
            );

            $type = CloudProviderType::from($typeValue);
        }

        $nameOption = $this->option('name');
        $name = $nameOption ?: text(
            label: 'Name for this provider',
            placeholder: "e.g. {$type->label()} Production",
            required: true,
        );

        $tokenOption = $this->option('token');
        $apiToken = $tokenOption ?: password(
            label: 'API token',
            required: true,
        );

        $this->components->info('Validating API token...');

        try {
            $cloudProvider = $cloudProviderClient->create(
                new CreateCloudProviderData(
                    name: $name,
                    type: $type,
                    apiToken: $apiToken,
                ),
            );
        } catch (LarakubeApiException $e) {
            $this->components->error($e->getMessage());

            return self::FAILURE;
        }

        $this->components->info("Cloud provider [{$cloudProvider->name}] added successfully.");

        return self::SUCCESS;
    }
}
