<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

final class SyncProviderRegionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('syncRegions', $this->route('provider'));
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [];
    }
}
