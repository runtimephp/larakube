<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\ManagementCluster;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreManagementClusterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', ManagementCluster::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:5', 'max:30'],
            'provider_id' => ['required', 'string', 'exists:providers,id'],
            'region_id' => ['required', 'string', 'exists:platform_regions,id'],
        ];
    }
}
