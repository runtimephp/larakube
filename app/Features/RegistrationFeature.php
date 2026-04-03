<?php

declare(strict_types=1);

namespace App\Features;

use Illuminate\Container\Attributes\Config;
use Illuminate\Support\Facades\App;

final readonly class RegistrationFeature
{
    public function __construct(
        #[Config('app.features.registration')] private bool $enabled = true,
    ) {}

    public function resolve(): bool
    {
        if (App::environment('local', 'testing')) {
            return true;
        }

        return $this->enabled;
    }
}
