<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Models\ManagementCluster;
use Illuminate\Foundation\Http\FormRequest;

final class StoreManagementClusterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', ManagementCluster::class);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'provider' => ['required', 'string', 'max:255'],
            'region' => ['required', 'string', 'max:255'],
        ];
    }
}
