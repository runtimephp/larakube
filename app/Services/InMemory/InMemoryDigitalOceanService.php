<?php

declare(strict_types=1);

namespace App\Services\InMemory;

use App\Contracts\CloudProviderService;

/**
 * In-memory implementation of CloudProviderService for testing.
 *
 * Allows tests to control validation behavior without making real API calls.
 */
final class InMemoryDigitalOceanService implements CloudProviderService
{
    private bool $shouldValidate = true;

    /**
     * Set whether the token should be considered valid.
     */
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
