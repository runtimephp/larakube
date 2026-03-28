<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Enums\CloudProviderType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class CreateCloudProviderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', Rule::enum(CloudProviderType::class)],
            'api_token' => ['nullable', 'string'],
        ];
    }
}
