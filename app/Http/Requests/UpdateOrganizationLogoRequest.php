<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Organization;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateOrganizationLogoRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Organization $organization */
        $organization = $this->route('organization');

        return $this->user()->can('updateSettings', $organization);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'logo' => ['required', 'image', 'max:2048'],
        ];
    }
}
