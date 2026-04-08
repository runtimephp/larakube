<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Enums\KubernetesVersion;
use App\Models\ManagementCluster;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreManagementClusterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', ManagementCluster::class);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'provider_id' => ['required', 'string', Rule::exists('providers', 'id')],
            'platform_region_id' => ['required', 'string', Rule::exists('platform_regions', 'id')],
            'version' => ['required', 'string', Rule::enum(KubernetesVersion::class)],
        ];
    }
}
