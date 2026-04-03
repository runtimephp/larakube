<?php

declare(strict_types=1);

namespace App\Features;

use Illuminate\Support\Facades\App;

final class LoginFeature
{
    public function resolve(?string $scope = null): bool
    {
        if (App::environment('local', 'testing')) {
            return true;
        }

        if ($scope && in_array($scope, self::allowedEmails(), true)) {
            return true;
        }

        return (bool) config('app.features.login', false);
    }

    /**
     * @return array<int, string>
     */
    private static function allowedEmails(): array
    {
        return array_filter(
            explode(',', (string) config('app.features.login_allowed_emails', ''))
        );
    }
}
