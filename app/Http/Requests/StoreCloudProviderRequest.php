<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\CloudProviderType;
use App\Models\CloudProvider;
use App\Models\Organization;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreCloudProviderRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Organization $organization */
        $organization = $this->route('organization');

        return $this->user()->can('manage', [CloudProvider::class, $organization]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', Rule::enum(CloudProviderType::class)],
            'api_token' => ['required', 'string'],
        ];
    }
}
