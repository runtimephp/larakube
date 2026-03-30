<?php

declare(strict_types=1);

namespace App\Actions;

use App\Contracts\StepHandler;
use App\Enums\InfrastructureStatus;
use App\Models\Infrastructure;

final readonly class MarkHealthy implements StepHandler
{
    public function handle(Infrastructure $infrastructure): void
    {
        $infrastructure->update([
            'status' => InfrastructureStatus::Healthy,
        ]);
    }
}
