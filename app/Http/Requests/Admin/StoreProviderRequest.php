<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\ProviderSlug;
use App\Models\Provider;
use App\Rules\ValidProviderToken;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreProviderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Provider::class);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $slug = ProviderSlug::tryFrom($this->string('slug')->toString());

        return [
            'slug' => ['required', Rule::enum(ProviderSlug::class), Rule::unique('providers', 'slug')],
            'api_token' => ['nullable', 'string', ...($slug ? [new ValidProviderToken($slug)] : [])],
        ];
    }
}
