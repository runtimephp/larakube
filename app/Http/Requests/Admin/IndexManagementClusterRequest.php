<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\ManagementCluster;
use Illuminate\Foundation\Http\FormRequest;

final class IndexManagementClusterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', ManagementCluster::class);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [];
    }
}
