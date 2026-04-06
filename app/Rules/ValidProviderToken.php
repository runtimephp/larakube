<?php

declare(strict_types=1);

namespace App\Rules;

use App\Enums\CloudProviderType;
use App\Enums\ProviderSlug;
use App\Services\CloudProviderFactory;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Translation\PotentiallyTranslatedString;

final readonly class ValidProviderToken implements ValidationRule
{
    public function __construct(private ProviderSlug $slug) {}

    /**
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || $value === '') {
            return;
        }

        $type = CloudProviderType::tryFrom($this->slug->value);

        if (! $type instanceof CloudProviderType) {
            $fail("Token validation is not supported for {$this->slug->label()}.");

            return;
        }

        $factory = app(CloudProviderFactory::class);

        try {
            $isValid = $factory->makeForValidation($type, $value)->validateToken();
        } catch (ConnectionException) {
            $fail("We couldn't verify the API token for {$this->slug->label()} right now. Please try again.");

            return;
        }

        if (! $isValid) {
            $fail("The API token for {$this->slug->label()} is invalid.");
        }
    }
}
