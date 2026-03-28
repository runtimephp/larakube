<?php

declare(strict_types=1);

namespace App\Services\InMemory;

use App\Contracts\CloudProviderService;

final class InMemoryMultipassService implements CloudProviderService
{
    private bool $shouldValidate = true;

    public function setValidationResult(bool $valid): self
    {
        $this->shouldValidate = $valid;

        return $this;
    }

    public function validateToken(): bool
    {
        return $this->shouldValidate;
    }
}
