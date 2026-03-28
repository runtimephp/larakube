<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

final class CreateServerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string'],
            'image' => ['required', 'string'],
            'region' => ['required', 'string'],
            'cloud_provider_id' => ['required', 'string', 'exists:cloud_providers,id'],
        ];
    }
}
