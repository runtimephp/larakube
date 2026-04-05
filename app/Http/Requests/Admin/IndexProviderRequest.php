<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\Provider;
use Illuminate\Foundation\Http\FormRequest;

final class IndexProviderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', Provider::class);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [];
    }
}
