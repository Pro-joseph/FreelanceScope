<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEstimateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'hourly_rate' => ['sometimes', 'numeric', 'min:0'],
            'total_hours' => ['sometimes', 'numeric', 'min:0'],
        ];
    }
}
