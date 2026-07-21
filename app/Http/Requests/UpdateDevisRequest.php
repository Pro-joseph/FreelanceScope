<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDevisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'conditions' => ['nullable', 'string', 'max:2000'],
            'total_amount' => ['nullable', 'numeric', 'min:0'],
            'status' => ['sometimes', 'string', 'in:draft,sent,accepted,refused'],
        ];
    }
}
