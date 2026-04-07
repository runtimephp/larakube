<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\Provider;
use App\Rules\ValidProviderToken;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateProviderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('provider'));
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        /** @var Provider $provider */
        $provider = $this->route('provider');

        return [
            'api_token' => ['nullable', 'string', new ValidProviderToken($provider->slug)],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
