<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\ManagementCluster;
use Illuminate\Foundation\Http\FormRequest;

class CreateManagementClusterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', ManagementCluster::class);
    }
}
