<?php

declare(strict_types=1);

namespace App\Features;

use Illuminate\Support\Facades\App;

final class RegistrationFeature
{
    public function resolve(): bool
    {
        if (App::environment('local', 'testing')) {
            return true;
        }

        return (bool) config('app.features.registration', false);
    }
}
