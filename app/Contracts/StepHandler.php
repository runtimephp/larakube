<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Infrastructure;

/**
 * @see ADR-0005 — Superseded by CAPI; scheduled for removal
 */
interface StepHandler
{
    public function handle(Infrastructure $infrastructure): void;
}
