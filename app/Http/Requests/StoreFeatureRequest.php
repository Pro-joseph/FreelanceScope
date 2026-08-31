<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFeatureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'complexity' => ['nullable', 'string', 'in:simple,moyen,complexe'],
            'hourly_rate' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'total_hours' => ['nullable', 'numeric', 'min:0', 'max:100000'],
        ];
    }
}
