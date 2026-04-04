<?php

declare(strict_types=1);

namespace App\Features;

use Illuminate\Container\Attributes\Config;
use Illuminate\Support\Facades\App;

final readonly class RegistrationFeature
{
    /**
     * @param  array<int, string>  $allowedEmails
     */
    public function __construct(
        #[Config('app.features.registration')] private bool $enabled = true,
        #[Config('app.features.registration_allowed_emails')] private array $allowedEmails = [],
    ) {}

    public function resolve(?string $scope = null): bool
    {
        if (App::environment('local', 'testing')) {
            return true;
        }

        if ($scope && in_array($scope, $this->allowedEmails, true)) {
            return true;
        }

        return $this->enabled;
    }
}
