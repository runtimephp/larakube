<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Rules\OrganizationName;
use Illuminate\Foundation\Http\FormRequest;

final class StoreOrganizationRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255', new OrganizationName],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
