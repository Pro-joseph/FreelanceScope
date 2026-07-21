<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDevisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => [
                'required',
                'integer',
                Rule::exists('clients', 'id')->where('user_id', auth()->id()),
            ],
            'project_id' => [
                'required',
                'integer',
                Rule::exists('projects', 'id'),
            ],
            'conditions' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
