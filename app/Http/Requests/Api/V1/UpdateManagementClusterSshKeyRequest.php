<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateManagementClusterSshKeyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('management_cluster'));
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'ssh_private_key' => ['required', 'string'],
        ];
    }
}
