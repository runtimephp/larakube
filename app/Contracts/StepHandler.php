<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Infrastructure;

interface StepHandler
{
    public function handle(Infrastructure $infrastructure): void;
}
