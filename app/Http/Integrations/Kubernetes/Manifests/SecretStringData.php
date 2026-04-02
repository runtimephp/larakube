<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Manifests;

use SensitiveParameter;

final readonly class SecretStringData
{
    /**
     * @param  array<string, string>  $values
     */
    public function __construct(
        #[SensitiveParameter] public array $values,
    ) {}

    /**
     * @return array<string, string>
     */
    public function toEncodedArray(): array
    {
        $encodedValues = [];

        foreach ($this->values as $key => $value) {
            $encodedValues[$key] = base64_encode($value);
        }

        return $encodedValues;
    }
}
