<?php

declare(strict_types=1);

use App\Actions\MarkHealthy;
use App\Enums\InfrastructureStatus;
use App\Models\Infrastructure;

test('marks infrastructure as healthy',
    /**
     * @throws Throwable
     */
    function (): void {
        /** @var Infrastructure $infrastructure */
        $infrastructure = Infrastructure::factory()->provisioning()->createQuietly();

        expect($infrastructure->status)->toBe(InfrastructureStatus::Provisioning);

        $action = new MarkHealthy();
        $action->handle($infrastructure);

        $infrastructure->refresh();

        expect($infrastructure->status)->toBe(InfrastructureStatus::Healthy);
    });
